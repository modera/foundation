<?php

namespace Modera\FileRepositoryBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Imagine\Image\ImageInterface;
use Modera\FileRepositoryBundle\Entity\StoredFile;
use Modera\FileRepositoryBundle\Repository\FileRepository;
use Modera\FileRepositoryBundle\ThumbnailsGenerator\EmulatedUploadedFile;
use Modera\FileRepositoryBundle\ThumbnailsGenerator\Interceptor;
use Modera\FileRepositoryBundle\ThumbnailsGenerator\NotImageGivenException;
use Modera\FileRepositoryBundle\ThumbnailsGenerator\ThumbnailsGenerator;
use Modera\FileRepositoryBundle\ThumbnailsGenerator\ThumbnailsInterceptorInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @copyright 2016 Modera Foundation
 */
#[AsCommand(
    name: 'modera:file-repository:generate-thumbnails',
    description: 'Allows to generate thumbnails for already existing files.',
)]
class GenerateThumbnailsCommand extends Command
{
    /**
     * Thumbnail modes accepted by the "mode" option, indexed by their command line name.
     *
     * @var array<string, int>
     */
    private const MODES = [
        'inset' => ImageInterface::THUMBNAIL_INSET,
        'outbound' => ImageInterface::THUMBNAIL_OUTBOUND,
    ];

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly FileRepository $fr,
        private readonly ThumbnailsGenerator $generator,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('repository', InputArgument::REQUIRED, 'Technical name of a repository')
            ->addOption(
                'thumbnail',
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                'Dimensions are to be delimited by x, for example - 300x200'
            )
            ->addOption(
                'mode',
                null,
                InputOption::VALUE_REQUIRED,
                \sprintf(
                    'Either "%s", see ImageInterface::THUMBNAIL_* constants for more details',
                    \implode('" or "', \array_keys(self::MODES))
                )
            )
            ->addOption(
                'file-id',
                null,
                InputOption::VALUE_OPTIONAL,
                'Generate thumbnails only for a specific stored file id'
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'If given then no thumbnails will be generated but instead a report will be provided of what thumbnails are to be generated'
            )
            ->addOption(
                'update-config',
                null,
                InputOption::VALUE_OPTIONAL,
                'Specify "false" if you do not want to update repository\'s config so that uploaded files would have thumbnails generated automatically',
                true
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $name */
        $name = $input->getArgument('repository');
        $repository = $this->fr->getRepository($name);
        if (!$repository) {
            throw new \RuntimeException(\sprintf('Unable to find a repository with name "%s"', $name));
        }

        /** @var string[] $expectedThumbnailsConfig */
        $expectedThumbnailsConfig = $input->getOption('thumbnail');
        /** @var string|null $fileIdOption */
        $fileIdOption = $input->getOption('file-id');
        /** @var string|null $modeOption */
        $modeOption = $input->getOption('mode');

        $mode = $this->resolveMode($modeOption);
        $modeFlag = null !== $mode ? self::MODES[$mode] : null;

        // indexed by original file's ID
        $report = [];

        // fetching original files
        $dql = \sprintf('SELECT e.id FROM %s e WHERE e.alternativeOf IS NULL AND e.repository = ?0', StoredFile::class);
        $query = $this->em->createQuery($dql);
        $query->setParameter(0, $repository);

        // fetching specific file
        if (null !== $fileIdOption && '' !== \trim((string) $fileIdOption)) {
            $fileId = (int) $fileIdOption;
            /** @var StoredFile|null $storedFile */
            $storedFile = $this->em->getRepository(StoredFile::class)->find($fileId);
            if (!$storedFile) {
                throw new \RuntimeException(\sprintf('Unable to find a stored file with id "%d"', $fileId));
            }
            if ($storedFile->getRepository()->getName() !== $repository->getName()) {
                throw new \RuntimeException(\sprintf('Stored file "%d" does not belong to repository "%s"', $fileId, $name));
            }
            if ($storedFile->getAlternativeOf()) {
                $storedFile = $storedFile->getAlternativeOf();
            }
            $query = $this->em->createQuery($dql.' AND e.id = ?1');
            $query->setParameter(0, $repository);
            $query->setParameter(1, $storedFile->getId());
        }

        foreach ($query->getArrayResult() as $fileData) {
            /** @var array{'id': int} $fileData */
            $originalId = $fileData['id'];

            $existingThumbnails = [];
            $existingThumbnailLabels = [];
            $missingThumbnails = [];

            // fetching original file's alternatives
            $alternativesQuery = $this->em->createQuery(\sprintf('SELECT e.id, e.meta FROM %s e WHERE e.alternativeOf = ?0', StoredFile::class));
            $alternativesQuery->setParameter(0, $fileData['id']);

            foreach ($alternativesQuery->getArrayResult() as $alternativeData) {
                if (\is_array($alternativeData ?? null) && \is_array($alternativeData['meta'] ?? null)) {
                    if (\is_array($alternativeData['meta']['thumbnail'] ?? null)) {
                        /** @var array{
                         *      'width'?: int|string,
                         *      'height'?: int|string,
                         *      'mode'?: string,
                         * } $thumbnailConfig
                         */
                        $thumbnailConfig = $alternativeData['meta']['thumbnail'];

                        if (isset($thumbnailConfig['width']) && isset($thumbnailConfig['height'])) {
                            $existingDimensions = $thumbnailConfig['width'].'x'.$thumbnailConfig['height'];
                            $existingMode = $this->resolveMode($thumbnailConfig['mode'] ?? null, false);

                            $existingThumbnails[] = $existingDimensions;
                            $existingThumbnailLabels[] = $this->formatThumbnail($existingDimensions, $existingMode);
                        }
                    }
                }
            }

            foreach ($expectedThumbnailsConfig as $expectedThumbnailDimensions) {
                if (!\in_array($expectedThumbnailDimensions, $existingThumbnails)) {
                    $missingThumbnails[] = $expectedThumbnailDimensions;
                }
            }

            $report[$originalId] = [
                'existing' => $existingThumbnails,
                'existing_labels' => $existingThumbnailLabels,
                'missing' => $missingThumbnails,
            ];
        }

        if (0 === \count($report)) {
            $output->writeln('No thumbnails to generate');

            return Command::SUCCESS;
        }

        if ($input->getOption('dry-run')) {
            $headers = ['ID', 'Filename', 'Missing thumbnails', 'Existing thumbnails'];
            $rows = [];

            foreach ($report as $id => $entry) {
                /** @var StoredFile $storedFile */
                $storedFile = $this->em->getRepository(StoredFile::class)->find($id);

                $missingLabels = \array_map(
                    fn (string $dimensions): string => $this->formatThumbnail($dimensions, $mode),
                    $entry['missing']
                );

                $missingOnes = \count($missingLabels) > 0 ? \implode(', ', $missingLabels) : '-';
                $existingOnes = \count($entry['existing_labels']) > 0 ? \implode(', ', $entry['existing_labels']) : '-';

                $rows[] = [$id, $storedFile->getFilename(), $missingOnes, $existingOnes];
            }

            $table = new Table($output);
            $table->setHeaders($headers);
            $table->setRows($rows);
            $table->render();

            return Command::SUCCESS;
        }

        foreach ($report as $id => $entry) {
            /** @var StoredFile $originalStoredFile */
            $originalStoredFile = $this->em->getRepository(StoredFile::class)->find($id);

            $output->writeln(\sprintf(' # Processing (%d) %s', $originalStoredFile->getId(), $originalStoredFile->getFilename()));

            foreach ($entry['missing'] as $dimensions) {
                list($width, $height) = \explode('x', $dimensions);

                /** @var non-falsy-string $originalPathname */
                $originalPathname = \tempnam(\sys_get_temp_dir(), 'file_');
                \file_put_contents($originalPathname, $originalStoredFile->getContents());

                $image = new File($originalPathname);

                try {
                    $thumbnailPathname = $this->generator->generate($image, (int) $width, (int) $height, $modeFlag);
                } catch (NotImageGivenException $e) {
                    $output->writeln('  * Skipping, file is not an image.');

                    continue;
                }

                // we need to use a subclass of UploadedFile class because it FileRepository
                // relies on its interface to properly determine mime, original mime type etc
                $thumbnailFile = new EmulatedUploadedFile(
                    $thumbnailPathname,
                    $originalStoredFile->getFilename(),
                    $originalStoredFile->getMimeType(),
                );

                $thumbnailStoredFile = $this->fr->put(
                    $repository->getName(),
                    $thumbnailFile,
                    [
                        'put_interceptor_filter' => function ($itc) {
                            // we are disabling thumbnails-generator-filter because if
                            // a repository has already this interceptor configured then putting thumbnails
                            // into repository will result in attempts to generate thumbnails for thumbnails ...
                            return !$itc instanceof ThumbnailsInterceptorInterface;
                        },
                        'after_interceptor_filter' => function ($itc) {
                            // we are disabling thumbnails-generator-filter because if
                            // a repository has already this interceptor configured then putting thumbnails
                            // into repository will result in attempts to generate thumbnails for thumbnails ...
                            return !$itc instanceof ThumbnailsInterceptorInterface;
                        },
                    ]
                );

                $thumbnailMeta = ['width' => $width, 'height' => $height];
                if (null !== $mode) {
                    $thumbnailMeta['mode'] = $mode;
                }

                $this->generator->updateStoredFileAlternativeMeta(
                    $thumbnailStoredFile,
                    $thumbnailMeta
                );

                $originalStoredFile->addAlternative($thumbnailStoredFile);

                // we don't need to keep a temporary file because file-repository by now should have
                // already stored a thumbnail file in its FS
                \unlink($thumbnailPathname);

                $this->em->flush();

                $output->writeln(\sprintf('  * %s', $this->formatThumbnail($dimensions, $mode)));
            }
        }

        if (true === $input->getOption('update-config')) {
            $isInterceptorAdded = false;
            $isThumbnailConfigUpdated = false;

            $repositoryConfig = $repository->getConfig();
            if (!is_array($repositoryConfig['interceptors'] ?? null)) {
                $repositoryConfig['interceptors'] = [];
            }
            if (!\in_array(Interceptor::ID, $repositoryConfig['interceptors']) && !\in_array(Interceptor::class, $repositoryConfig['interceptors'])) {
                $repositoryConfig['interceptors'][] = Interceptor::class;

                $isInterceptorAdded = true;
            }

            if (!isset($repositoryConfig['thumbnail_sizes'])) {
                $repositoryConfig['thumbnail_sizes'] = [];
            }

            $existingThumbnailsConfigEntries = [];
            foreach ($repositoryConfig['thumbnail_sizes'] as $thumbnailConfig) {
                if (isset($thumbnailConfig['width']) && isset($thumbnailConfig['height'])) {
                    $existingThumbnailsConfigEntries[] = $thumbnailConfig['width'].'x'.$thumbnailConfig['height'];
                }
            }

            foreach ($expectedThumbnailsConfig as $dimensions) {
                list($width, $height) = \explode('x', $dimensions);

                if (!\in_array($dimensions, $existingThumbnailsConfigEntries)) {
                    $repositoryConfig['thumbnail_sizes'][] = [
                        'width' => $width,
                        'height' => $height,
                    ];

                    $isThumbnailConfigUpdated = true;
                }
            }

            $repository->setConfig($repositoryConfig);

            $this->em->flush();

            $output->writeln(
                $isInterceptorAdded ? 'Interceptor integrated into repository' : 'Interceptor is already has been registered before, skipping ...'
            );
            $output->writeln(
                $isThumbnailConfigUpdated ? 'Thumbnails config updated for repository' : 'Repository already contains necessary thumbnails config, skipping ...'
            );
        }

        return Command::SUCCESS;
    }

    /**
     * @return string|null A name of one of the self::MODES entries
     */
    private function resolveMode(mixed $mode, bool $strict = true): ?string
    {
        if (!\is_string($mode) || '' === \trim($mode)) {
            return null;
        }

        $mode = \trim($mode);
        if (!\array_key_exists($mode, self::MODES)) {
            if ($strict) {
                throw new InvalidArgumentException(\sprintf(
                    'Unsupported thumbnail mode "%s" given, expected one of - %s',
                    $mode,
                    \implode(', ', \array_keys(self::MODES))
                ));
            }

            return null;
        }

        return $mode;
    }

    private function formatThumbnail(string $dimensions, ?string $mode): string
    {
        return null !== $mode ? \sprintf('%s (%s)', $dimensions, $mode) : $dimensions;
    }
}

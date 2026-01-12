<?php

namespace Modera\FileRepositoryBundle\ThumbnailsGenerator;

use Modera\FileRepositoryBundle\Entity\Repository;
use Modera\FileRepositoryBundle\Entity\StoredFile;
use Modera\FileRepositoryBundle\Intercepting\BaseOperationInterceptor;
use Modera\SchedulerBundle\Service\Scheduler;

/**
 * Generates thumbnails in a detached process by dispatching the console command.
 *
 * @copyright 2025 Modera Foundation
 */
class CommandInterceptor extends BaseOperationInterceptor
{
    // TODO: remove, BC
    public const ID = 'modera_file_repository.interceptors.thumbnails_generator.command_interceptor';

    public function __construct(
        private readonly Scheduler $scheduler,
        private readonly ?string $deploymentName
    ) {
    }

    private function isAlternative(\SplFileInfo $file): bool
    {
        return \in_array(AlternativeFileTrait::class, \class_uses(\get_class($file)));
    }

    public function onPut(StoredFile $storedFile, \SplFileInfo $file, Repository $repository, array $context = []): void
    {

    }

    public function afterPut(StoredFile $storedFile, \SplFileInfo $file, Repository $repository, array $context = []): void
    {
        $this->scheduleCommand($storedFile, $file, $repository);
    }

    private function scheduleCommand(StoredFile $storedFile, \SplFileInfo $file, Repository $repository): void
    {
        /** @var array{'thumbnail_sizes'?: array<array{'width'?: int, 'height'?: int}>} $repoConfig */
        $repoConfig = $repository->getConfig();
        if (!isset($repoConfig['thumbnail_sizes']) || 0 === \count($repoConfig['thumbnail_sizes'])) {
            return;
        }

        if ($this->isAlternative($file)) {
            return;
        }

        $mimeType = $storedFile->getMimeType();
        if (null !== $mimeType && '' !== $mimeType && 0 !== \strpos($mimeType, 'image/')) {
            return;
        }

        $sizes = [];
        foreach ($repoConfig['thumbnail_sizes'] as $thumbnailConfig) {
            if (isset($thumbnailConfig['width']) && isset($thumbnailConfig['height'])) {
                $sizes[] = $thumbnailConfig['width'].'x'.$thumbnailConfig['height'];
            }
        }
        $sizes = \array_values(\array_unique($sizes));
        if (0 === \count($sizes)) {
            return;
        }

        $command = sprintf(
            '%s %s %s %s',
            'modera:file-repository:generate-thumbnails',
            $repository->getName(),
            '--file-id='.$storedFile->getId(),
            '--update-config=false',
        );

        foreach ($sizes as $dimensions) {
            $command  .= ' --thumbnail='.$dimensions;
        }

        if ($this->deploymentName) {
            $command = 'MODERA_SD_NAME=' . escapeshellarg($this->deploymentName) . ' /var/www/bin/console '. $command;
        }

        $type = $this->scheduler::OS_SHELL;
        $this->scheduler
            ->command($command)
            ->setType($type)
            ->run();
    }
}

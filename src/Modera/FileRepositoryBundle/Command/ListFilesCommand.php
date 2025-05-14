<?php

namespace Modera\FileRepositoryBundle\Command;

use Modera\FileRepositoryBundle\Repository\FileRepository;
use Modera\FileRepositoryBundle\Util\StoredFileUtils;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @copyright 2014 Modera Foundation
 */
#[AsCommand(
    name: 'modera:file-repository:list-files',
    description: 'Allows to see files in a repository',
)]
class ListFilesCommand extends Command
{
    use TableTrait;

    public function __construct(
        private readonly FileRepository $fr,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('repository-name', InputArgument::REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $repositoryName */
        $repositoryName = $input->getArgument('repository-name');
        $repository = $this->fr->getRepository($repositoryName);

        if (!$repository) {
            throw new \RuntimeException(\sprintf('Unable to find a repository with given name "%s"!', $repositoryName));
        }

        $rows = [];
        foreach ($repository->getFiles() as $storedFile) {
            $rows[] = [
                $storedFile->getId(),
                $storedFile->getFilename(),
                $storedFile->getMimeType(),
                StoredFileUtils::formatFileSize($storedFile->getSize()),
                $storedFile->getCreatedAt()->format('d.m.Y H:i'),
                $storedFile->getOwner(),
            ];
        }

        $this->renderTable(
            $output,
            ['#', 'Filename', 'Mime type', 'Size', 'Created', 'Owner'],
            $rows
        );

        return Command::SUCCESS;
    }
}

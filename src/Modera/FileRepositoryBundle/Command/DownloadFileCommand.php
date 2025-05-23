<?php

namespace Modera\FileRepositoryBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Modera\FileRepositoryBundle\Entity\StoredFile;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @copyright 2014 Modera Foundation
 */
#[AsCommand(
    name: 'modera:file-repository:download-file',
    description: 'Downloads a file to local filesystem',
)]
class DownloadFileCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('file_id', InputArgument::REQUIRED)
            ->addArgument('local_path', InputArgument::REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var int|string $fileId */
        $fileId = $input->getArgument('file_id');

        /** @var ?StoredFile $storedFile */
        $storedFile = $this->em->getRepository(StoredFile::class)->find($fileId);
        if (!$storedFile) {
            throw new \RuntimeException(\sprintf('Unable to find a file with ID "%s".', $fileId));
        }

        /** @var string $localPath */
        $localPath = $input->getArgument('local_path');

        $output->writeln('Downloading the file ...');

        \ob_start();
        $result = \file_put_contents($localPath, $storedFile->getContents());
        /** @var string $errorOutput */
        $errorOutput = \ob_get_clean();

        if (false !== $result) {
            $output->writeln(\sprintf(
                '<info>File from repository "%s" has been successfully downloaded and stored locally at %s</info>',
                $storedFile->getRepository()->getName(),
                $localPath
            ));
        } else {
            $output->writeln('<error>Something went wrong, we were unable to save a file locally: </error>');
            $output->writeln($errorOutput);
        }

        return Command::SUCCESS;
    }
}

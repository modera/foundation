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
    name: 'modera:file-repository:delete-file',
    description: 'Deletes a file from repository',
)]
class DeleteFileCommand extends Command
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
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $fileId */
        $fileId = $input->getArgument('file_id');

        /** @var ?StoredFile $storedFile */
        $storedFile = $this->em->find(StoredFile::class, $fileId);
        if (!$storedFile) {
            throw new \RuntimeException('Unable to find a file with ID '.$fileId);
        }

        $output->writeln(\sprintf(
            'Deleting file "%s" from repository "%s"',
            $storedFile->getFilename(),
            $storedFile->getRepository()->getName()
        ));

        $this->em->remove($storedFile);
        $this->em->flush();

        $output->writeln('<info>Done!</info>');

        return Command::SUCCESS;
    }
}

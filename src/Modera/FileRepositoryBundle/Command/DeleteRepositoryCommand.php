<?php

namespace Modera\FileRepositoryBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Modera\FileRepositoryBundle\Repository\FileRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * @copyright 2014 Modera Foundation
 */
#[AsCommand(
    name: 'modera:file-repository:delete-repository',
    description: 'Deletes a repository with all its files',
)]
class DeleteRepositoryCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly FileRepository $fr,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('repository', InputArgument::REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $name */
        $name = $input->getArgument('repository');
        $repository = $this->fr->getRepository($name);
        if (!$repository) {
            throw new \RuntimeException(sprintf('Unable to find a repository with name "%s"', $name));
        }

        if (\count($repository->getFiles()) > 0) {
            /** @var HelperSet $helperSet */
            $helperSet = $this->getHelperSet();

            /** @var QuestionHelper $questionHelper */
            $questionHelper = $helperSet->get('question');

            $question = sprintf(
                'Repository "%s" contains %s files, are you sure that you want to delete this repository with all these files ? [Y/n]: ',
                $repository->getName(),
                \count($repository->getFiles())
            );
            $question = new Question($question);

            $answer = $questionHelper->ask($input, $output, $question);
            if ($answer) {
                $output->writeln(\sprintf('Deleting repository "%s"', $repository->getName()));

                $this->em->remove($repository);
                $this->em->flush();

                $output->writeln('Done!');
            } else {
                $output->writeln('Aborting ...');
            }
        } else {
            $output->writeln(\sprintf('Deleting repository "%s"', $repository->getName()));

            $this->em->remove($repository);
            $this->em->flush();

            $output->writeln('Done!');
        }

        return Command::SUCCESS;
    }
}

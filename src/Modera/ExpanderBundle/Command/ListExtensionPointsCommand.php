<?php

namespace Modera\ExpanderBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * @copyright 2024 Modera Foundation
 */
#[AsCommand(
    name: 'modera:expander:list-extension-points',
    description: 'Shows a list of available extension-points.',
)]
class ListExtensionPointsCommand extends AbstractCommand
{
    protected function configure(): void
    {
        $this
            ->addArgument('id-filter', null, 'Allows to filter displayed extension points')
            ->addOption('skip-question', null, null, 'If given then command will not ask a user to type in command # to display its detailed description.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $app = $this->getApplication();
        if (null === $app) {
            throw new \RuntimeException('Application not defined.');
        }

        $app->setAutoExit(false);

        /** @var ?string $idFilter */
        $idFilter = $input->getArgument('id-filter');

        $i = 0;
        /** @var mixed[] $rows */
        $rows = [];
        foreach ($this->getExtensionPoints() as $ep) {
            if (\is_string($idFilter) && false === \strpos($ep->getId(), $idFilter)) {
                continue;
            }

            $rows[] = [
                $i + 1,
                $ep->getId(),
                $ep->getDetailedDescription() ? 'Yes' : 'No',
                $ep->getDescription(),
            ];

            ++$i;
        }

        $table = new Table($output);
        $table
            ->setHeaders(['#', 'ID', 'Docs', 'Description'])
            ->setRows($rows)
        ;
        $table->render();

        if (!$input->getOption('skip-question')) {
            /** @var QuestionHelper $questionHelper */
            $questionHelper = $this->getHelper('question');

            $question = new Question('Extension point # you want to see detailed documentation for: ');

            /** @var ?int $answer */
            $answer = $questionHelper->ask($input, $output, $question);
            if (null === $answer) {
                return Command::SUCCESS;
            }

            $extensionPointId = $rows[$answer - 1][1];
            $app->run(new StringInput('modera:expander:explore-extension-point '.$extensionPointId));

            $question = 'Would you like to create a contribution to this extension-point right away ? ';
            $output->writeln(\str_repeat('-', \strlen($question)));
            $question = new ConfirmationQuestion($question, false);

            /** @var bool $answer */
            $answer = $questionHelper->ask($input, $output, $question);

            if (false !== $answer) {
                $output->writeln('');
                $app->run(new StringInput('modera:expander:contribute '.$extensionPointId));
            }
        }

        return Command::SUCCESS;
    }
}

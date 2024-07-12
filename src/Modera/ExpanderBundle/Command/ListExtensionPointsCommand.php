<?php

namespace Modera\ExpanderBundle\Command;

use Modera\ExpanderBundle\DependencyInjection\CompositeContributorsProviderCompilerPass;
use Modera\ExpanderBundle\Misc\KernelProxy;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class ListExtensionPointsCommand extends AbstractCommand
{
    protected function configure(): void
    {
        $this
            ->setName('modera:expander:list-extension-points')
            ->setDescription('Shows a lists of available extension-points.')
            ->addArgument('id-filter', null, 'Allows to filter displayed extension points')
            ->addOption('skip-question', null, null, 'If given then command will not ask a user to type in command # to display its detailed description.')
        ;
    }

    protected function doExecute(KernelProxy $kernelProxy, InputInterface $input, OutputInterface $output): void
    {
        $app = $this->getApplication();
        if (!$app) {
            throw new \RuntimeException('Application not defined.');
        }

        $app->setAutoExit(false);

        /** @var string $idFilter */
        $idFilter = $input->getArgument('id-filter');

        $i = 0;
        /** @var mixed[] $rows */
        $rows = [];
        /** @var CompositeContributorsProviderCompilerPass $pass */
        foreach ($kernelProxy->getExtensionCompilerPasses() as $pass) {
            $ep = $pass->getExtensionPoint();

            if (!$ep || (null !== $idFilter && false === \strpos($ep->getId(), $idFilter))) {
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
            ->setHeaders(['#', 'Name', 'Docs', 'Description'])
            ->setRows($rows)
        ;
        $table->render();

        if (!$input->getOption('skip-question')) {
            /** @var QuestionHelper $questionHelper */
            $questionHelper = $this->getHelper('question');

            $question = new Question('Extension point # you want to see detailed documentation for: ');

            /** @var ?int $answer */
            $answer = $questionHelper->ask($input, $output, $question);

            $extensionPointId = null;

            if (null !== $answer) {
                $extensionPointId = $rows[$answer - 1][1];
                $app->run(new StringInput('modera:expander:explore-extension-point '.$extensionPointId));
            }

            $question = 'Would you like to create a contribution to this extension-point right away ? ';
            $output->writeln(\str_repeat('-', \strlen($question)));
            $question = new ConfirmationQuestion($question);

            /** @var bool $answer */
            $answer = $questionHelper->ask($input, $output, $question);

            if ($answer) {
                $output->writeln('');
                $app->run(new StringInput('modera:expander:contribute '.$extensionPointId));
            }
        }
    }
}

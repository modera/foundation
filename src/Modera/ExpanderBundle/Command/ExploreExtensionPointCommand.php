<?php

namespace Modera\ExpanderBundle\Command;

use Modera\ExpanderBundle\Ext\ExtensionPoint;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @copyright 2024 Modera Foundation
 */
#[AsCommand(
    name: 'modera:expander:explore-extension-point',
    description: 'Provides detailed information about an extension point.',
)]
class ExploreExtensionPointCommand extends AbstractCommand
{
    protected function configure(): void
    {
        $this
            ->addArgument('id', InputArgument::REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $idArg */
        $idArg = $input->getArgument('id');

        /** @var ?ExtensionPoint $extensionPoint */
        $extensionPoint = null;

        foreach ($this->getExtensionPoints() as $iteratedExtensionPoint) {
            if ($iteratedExtensionPoint->getId() === $idArg) {
                $extensionPoint = $iteratedExtensionPoint;
            }
        }

        if (!$extensionPoint) {
            throw new \RuntimeException(\sprintf('Extension point with ID "%s" is not found.', $idArg));
        }

        $output->writeln('');
        $output->writeln('<info>ID:</info>');
        $output->writeln($extensionPoint->getId());
        $output->writeln('');

        $output->writeln('<info>Service id:</info>');
        $output->writeln($extensionPoint->getServiceId());
        $output->writeln('');

        $output->writeln('<info>Contribution tag:</info>');
        $output->writeln($extensionPoint->getContributionTag());
        $output->writeln('');

        $output->writeln('<info>Category:</info>');
        $output->writeln($extensionPoint->getCategory() ?: '-');
        $output->writeln('');

        $output->writeln('<info>Description:</info>');
        $output->writeln($extensionPoint->getDescription() ?: '-');
        $output->writeln('');

        $output->writeln('<info>Detailed description:</info>');
        $output->writeln($extensionPoint->getDetailedDescription() ?: '-');
        $output->writeln('');

        return Command::SUCCESS;
    }
}

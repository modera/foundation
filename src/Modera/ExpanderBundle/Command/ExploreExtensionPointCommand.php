<?php

namespace Modera\ExpanderBundle\Command;

use Modera\ExpanderBundle\DependencyInjection\CompositeContributorsProviderCompilerPass;
use Modera\ExpanderBundle\Ext\ExtensionPoint;
use Modera\ExpanderBundle\Misc\KernelProxy;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExploreExtensionPointCommand extends AbstractCommand
{
    protected function configure(): void
    {
        $this
            ->setName('modera:expander:explore-extension-point')
            ->addArgument('id', InputArgument::REQUIRED)
            ->setDescription('Provides detailed information about an extension point.')
        ;
    }

    protected function doExecute(KernelProxy $kernelProxy, InputInterface $input, OutputInterface $output): void
    {
        /** @var string $idArg */
        $idArg = $input->getArgument('id');

        /** @var ?ExtensionPoint $extensionPoint */
        $extensionPoint = null;

        /** @var CompositeContributorsProviderCompilerPass $pass */
        foreach ($kernelProxy->getExtensionCompilerPasses() as $pass) {
            $iteratedExtensionPoint = $pass->getExtensionPoint();

            if ($iteratedExtensionPoint && $iteratedExtensionPoint->getId() === $idArg) {
                $extensionPoint = $iteratedExtensionPoint;
            }
        }

        if (!$extensionPoint) {
            throw new \RuntimeException("Extension point with ID '$idArg' is not found.");
        }

        $output->writeln('<info>ID:</info>');
        $output->writeln($extensionPoint->getId());

        $output->writeln('<info>Contribution tag:</info>');
        $output->writeln($extensionPoint->getContributionTag());

        $output->writeln('<info>Category:</info>');
        $output->writeln($extensionPoint->getCategory() ?: '-');

        $output->writeln('<info>Description:</info>');
        $output->writeln($extensionPoint->getDescription() ?: '-');

        $output->writeln('<info>Detailed description:</info>');
        $output->writeln($extensionPoint->getDetailedDescription() ?: '-');
    }
}

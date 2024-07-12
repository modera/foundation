<?php

namespace Modera\ExpanderBundle\Command;

use Modera\ExpanderBundle\Misc\KernelProxy;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class ContributeCommand extends AbstractCommand
{
    protected function configure(): void
    {
        $this
            ->setName('modera:expander:contribute')
            ->addArgument('id', InputArgument::REQUIRED, 'Extension point ID')
            ->addArgument('bundle-filter')
            ->setDescription('Allow to create a contribute for an extension point.')
        ;
    }

    private function isAppropriateBundle(BundleInterface $bundle): bool
    {
        // we are not going to let to contribute to bundle with these namespaces
        $ignores = ['Symfony', 'Sensio', 'Doctrine', 'Knp', 'FOS'];

        foreach ($ignores as $namespace) {
            if ($namespace === \substr($bundle->getNamespace(), 0, \strlen($namespace))) {
                return false;
            }
        }

        return true;
    }

    protected function doExecute(KernelProxy $kernelProxy, InputInterface $input, OutputInterface $output): void
    {
        /** @var string $idArg */
        $idArg = $input->getArgument('id');

        /** @var ?string $bundleFilterArg */
        $bundleFilterArg = $input->getArgument('bundle-filter');

        $extensionPoint = $kernelProxy->getExtensionPoint($idArg);
        if (!$extensionPoint) {
            throw new \RuntimeException("Unable to find an extension point with ID '$idArg'.");
        }

        /** @var BundleInterface[] $bundlesToIterate */
        $bundlesToIterate = [];
        if (null !== $bundleFilterArg) {
            foreach ($kernelProxy->getBundles() as $bundle) {
                if (false !== \strpos($bundle->getName(), $bundleFilterArg)) {
                    $bundlesToIterate[] = $bundle;
                }
            }
        } else {
            $bundlesToIterate = $kernelProxy->getBundles();
        }

        if (1 === \count($bundlesToIterate)) {
            $bundleToGenerateTo = $bundlesToIterate[0];
        } elseif (\count($bundlesToIterate) > 1) {
            /** @var BundleInterface[] $bundles */
            $bundles = [];
            /** @var mixed[] $rows */
            $rows = [];
            foreach ($bundlesToIterate as $bundle) {
                if (!$this->isAppropriateBundle($bundle)) {
                    continue;
                }

                $index = \count($rows) + 1;
                $bundles[$index] = $bundle;
                $rows[] = [$index, $bundle->getName(), $bundle->getPath()];
            }

            $table = new Table($output);
            $table
                ->setHeaders(['#', 'Name', 'Location'])
                ->setRows($rows)
            ;
            $table->render();

            /** @var QuestionHelper $questionHelper */
            $questionHelper = $this->getHelper('question');
            $question = new Question('<info>Please specify bundle # you want to contribute to:</info> ');
            $bundleIndex = $questionHelper->ask($input, $output, $question);

            if (!isset($bundles[$bundleIndex])) {
                throw new \RuntimeException('Unable to find a bundle with given index.');
            }

            $bundleToGenerateTo = $bundles[$bundleIndex];
        } else {
            throw new \RuntimeException("Unable to find any bundles which match given '$bundleFilterArg' filter.");
        }

        $generator = $extensionPoint->getContributionGenerator();
        if (!$generator) {
            throw new \RuntimeException(\sprintf("It turns out that extension point '%s' doesn't support contribution generation.", $extensionPoint->getId()));
        }

        $generator->generate($bundleToGenerateTo, $extensionPoint, $input, $output, $this->getHelperSet());
    }
}

<?php

namespace Modera\ExpanderBundle\Command;

use Modera\ExpanderBundle\Generation\ContributionGeneratorInterface;
use Modera\ExpanderBundle\Generation\StandardContributionGenerator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @copyright 2024 Modera Foundation
 */
#[AsCommand(
    name: 'modera:expander:contribute',
    description: 'Allow to create a contribute for an extension point.',
)]
class ContributeCommand extends AbstractCommand
{
    private ?ContributionGeneratorInterface $contributionGenerator = null;

    /**
     * @internal For tests
     */
    public function setContributionGenerator(?ContributionGeneratorInterface $contributionGenerator): void
    {
        $this->contributionGenerator = $contributionGenerator;
    }

    public function __construct(
        private readonly KernelInterface $kernel,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('id', InputArgument::REQUIRED, 'Extension point ID')
            ->addArgument('bundle-filter')
        ;
    }

    private function isAppropriateBundle(BundleInterface $bundle): bool
    {
        // we are not going to let to contribute to bundle with these namespaces
        $ignores = ['Doctrine', 'FOS', 'Knp', 'Sensio', 'Symfony', 'Twig'];

        foreach ($ignores as $namespace) {
            if ($namespace === \substr($bundle->getNamespace(), 0, \strlen($namespace))) {
                return false;
            }
        }

        return true;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $idArg */
        $idArg = $input->getArgument('id');

        /** @var ?string $bundleFilterArg */
        $bundleFilterArg = $input->getArgument('bundle-filter');

        $extensionPoint = $this->getExtensionPoint($idArg);
        if (!$extensionPoint) {
            throw new \RuntimeException(\sprintf('Unable to find an extension point with ID "%s".', $idArg));
        }

        /** @var BundleInterface[] $bundlesToIterate */
        $bundlesToIterate = [];
        if (null !== $bundleFilterArg) {
            foreach ($this->kernel->getBundles() as $bundle) {
                if (\str_contains($bundle->getName(), $bundleFilterArg)) {
                    $bundlesToIterate[] = $bundle;
                }
            }
        } else {
            $bundlesToIterate = $this->kernel->getBundles();
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
            if (null === $bundleIndex) {
                return Command::SUCCESS;
            }

            if (!isset($bundles[$bundleIndex])) {
                throw new \RuntimeException('Unable to find a bundle with given index.');
            }

            $bundleToGenerateTo = $bundles[$bundleIndex];
        } else {
            throw new \RuntimeException("Unable to find any bundles which match given '$bundleFilterArg' filter.");
        }

        $generator = $this->contributionGenerator ?? new StandardContributionGenerator([
            'className' => null,
            'dirName' => null,
        ]);

        $generator->generate($bundleToGenerateTo, $extensionPoint, $input, $output, $this->getHelperSet());

        return Command::SUCCESS;
    }
}

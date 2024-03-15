<?php

namespace Modera\ExpanderBundle\Contributing;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * For every extension point contributed by bundles which implement ExtensionPointsAwareBundleInterface this
 * compiles pass will dynamically contribute a provider class to DI container, so later you can see a container
 * dump file to find all contributions for a certain extension-point.
 *
 * This compiler pass will be automatically registered if when you added {@class \Modera\ExpanderBundle\ModeraExpanderBundle}
 * to your app kernel you provided a link to kernel as its first argument.
 */
class ExtensionPointsAwareBundlesCollectorCompilerPass implements CompilerPassInterface
{
    private ?KernelInterface $kernel;

    public function __construct(?KernelInterface $kernel = null)
    {
        $this->kernel = $kernel;
    }

    private function createServiceName(string $bundleName, string $extensionPointName): string
    {
        return \strtolower($bundleName.'.dynamic_contribution.'.\str_replace('.', '_', $extensionPointName));
    }

    /**
     * @return array<BundleInterface&ExtensionPointsAwareBundleInterface>
     */
    private function getExtensionPointsAwareBundles(ContainerBuilder $container): array
    {
        $bundles = [];
        if ($this->kernel) {
            foreach ($this->kernel->getBundles() as $bundle) {
                if ($bundle instanceof ExtensionPointsAwareBundleInterface) {
                    $bundles[] = $bundle;
                }
            }
        } else {
            /** @var string[] $registeredBundles */
            $registeredBundles = $container->getParameter('kernel.bundles');
            foreach ($registeredBundles as $bundleName) {
                if (\is_subclass_of($bundleName, ExtensionPointsAwareBundleInterface::class)) {
                    /** @var BundleInterface&ExtensionPointsAwareBundleInterface $bundle */
                    $bundle = new $bundleName();
                    $bundles[] = $bundle;
                }
            }
        }

        return $bundles;
    }

    public function process(ContainerBuilder $container): void
    {
        foreach ($this->getExtensionPointsAwareBundles($container) as $bundle) {
            foreach ($bundle->getExtensionPointContributions() as $extensionPointName => $contributions) {
                $serviceName = $this->createServiceName($bundle->getName(), $extensionPointName);

                if ($container->hasDefinition($serviceName)) {
                    throw new \RuntimeException("Unable to dynamically register a new service with ID '$serviceName', this ID is already in use.");
                }

                $definitionArgs = [new Reference('kernel'), $bundle->getName(), $extensionPointName];
                $definition = new Definition(BundleContributorAdapter::class, $definitionArgs);
                $definition->addTag($extensionPointName);

                $container->setDefinition($serviceName, $definition);
            }
        }
    }
}

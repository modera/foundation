<?php

namespace Modera\ExpanderBundle\Contributing;

use Modera\ExpanderBundle\DependencyInjection\ExtensionPointsCompilerPassTrait;
use Modera\ExpanderBundle\Ext\ExtensionPoint;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

/**
 * For every extension point contributed by bundles which implement ExtensionPointsAwareBundleInterface this
 * compiles pass will dynamically contribute a provider class to DI container, so later you can see a container
 * dump file to find all contributions for a certain extension-point.
 *
 * @copyright 2024 Modera Foundation
 */
class ExtensionPointsAwareBundlesCollectorCompilerPass implements CompilerPassInterface
{
    use ExtensionPointsCompilerPassTrait;

    private function createServiceId(string $bundleName, string $extensionPointId): string
    {
        return \strtolower($bundleName.'.dynamic_contribution.'.\str_replace('.', '_', $extensionPointId));
    }

    /**
     * @return array<BundleInterface&ExtensionPointsAwareBundleInterface>
     */
    private function getExtensionPointsAwareBundles(ContainerBuilder $container): array
    {
        $bundles = [];
        /** @var string[] $registeredBundles */
        $registeredBundles = $container->getParameter('kernel.bundles');
        foreach ($registeredBundles as $bundleName) {
            if (\is_subclass_of($bundleName, ExtensionPointsAwareBundleInterface::class)) {
                /** @var BundleInterface&ExtensionPointsAwareBundleInterface $bundle */
                $bundle = new $bundleName();
                $bundles[] = $bundle;
            }
        }

        return $bundles;
    }

    public function process(ContainerBuilder $container): void
    {
        /** @var array<string, ExtensionPoint> $extensionPoints */
        $extensionPoints = [];
        foreach ($this->getExtensionPoints($container) as $extensionPoint) {
            $extensionPoints[$extensionPoint->getId()] = $extensionPoint;
        }

        foreach ($this->getExtensionPointsAwareBundles($container) as $bundle) {
            foreach ($bundle->getExtensionPointContributions() as $extensionPointId => $contributions) {
                $serviceId = $this->createServiceId($bundle->getName(), $extensionPointId);
                if ($container->hasDefinition($serviceId)) {
                    throw new \RuntimeException(\sprintf('Unable to dynamically register a new service with ID "%s", this ID is already in use.', $serviceId));
                }

                $extensionPoint = $extensionPoints[$extensionPointId] ?? null;
                if ($extensionPoint) {
                    $contributionTag = $extensionPoint->getContributionTag();
                } else {
                    // TODO: throw exception
                    // throw new \RuntimeException(\sprintf('Extension point with ID "%s" is not found.', $extensionPointId);
                    @\trigger_error(\sprintf('Using "ContributionTag" as identifier in %s::getExtensionPointContributions is deprecated, use "ExtensionPointId" instead.', $bundle::class), E_USER_DEPRECATED);
                    $contributionTag = $extensionPointId;
                }

                $definitionArgs = [new Reference('kernel'), $bundle->getName(), $extensionPointId];
                $definition = new Definition(BundleContributorAdapter::class, $definitionArgs);
                $definition->addTag($contributionTag);

                $container->setDefinition($serviceId, $definition);
            }
        }
    }
}

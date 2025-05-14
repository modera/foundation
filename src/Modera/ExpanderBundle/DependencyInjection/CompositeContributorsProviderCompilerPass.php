<?php

namespace Modera\ExpanderBundle\DependencyInjection;

use Modera\ExpanderBundle\Ext\AsContributorFor;
use Modera\ExpanderBundle\Ext\ChainMergeContributorsProvider;
use Modera\ExpanderBundle\Ext\ExtensionPoint;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @internal use \Modera\ExpanderBundle\Ext\ExtensionPoint::createCompilerPass() instead!
 *
 * The compiler pass will collect services from the constructor with a defined tag, and create a new service which may
 * be used later to get an aggregated value of their getItems method
 *
 * @copyright 2024 Modera Foundation
 */
class CompositeContributorsProviderCompilerPass implements CompilerPassInterface, ExtensionPointAwareCompilerPassInterface
{
    /**
     * @param string  $providerServiceId         This compiler class will contribute a new service with this ID to the
     *                                           container, it will be an instance of the ChainMergeContributorsProvider class
     * @param ?string $contributorServiceTagName And the aforementioned instance will collect services from the
     *                                           container which were tagger with this ID
     */
    public function __construct(
        private readonly string $providerServiceId,
        private readonly ?string $contributorServiceTagName = null,
        private readonly ?ExtensionPoint $extensionPoint = null,
    ) {
    }

    public function getProviderServiceId(): string
    {
        return $this->providerServiceId;
    }

    public function getContributorServiceTagName(): string
    {
        return $this->contributorServiceTagName ?? $this->providerServiceId;
    }

    public function getExtensionPoint(): ?ExtensionPoint
    {
        return $this->extensionPoint;
    }

    public function process(ContainerBuilder $container): void
    {
        $providerDef = new Definition(ChainMergeContributorsProvider::class);
        $providerDef->setPublic(true);
        $container->addDefinitions([
            $this->getProviderServiceId() => $providerDef,
        ]);

        $extensionPoint = $this->getExtensionPoint();
        if ($extensionPoint) {
            foreach ($container->findTaggedServiceIds('modera_expander.contributor', true) as $id => $tags) {
                $def = $container->getDefinition($id);
                $class = $def->getClass() ?? $id;
                if (!$reflectionClass = $container->getReflectionClass($class)) {
                    throw new InvalidArgumentException(\sprintf('Class "%s" used for service "%s" cannot be found.', $class, $id));
                }

                $attribute = $reflectionClass->getAttributes(AsContributorFor::class)[0] ?? null;
                /** @var ?AsContributorFor $asContributorFor */
                $asContributorFor = $attribute?->newInstance();
                if ($asContributorFor && $asContributorFor->id === $extensionPoint->getId()) {
                    $providerDef->addMethodCall('addContributor', [new Reference($id)]);
                }
            }
        }

        $contributors = $container->findTaggedServiceIds($this->getContributorServiceTagName());
        foreach ($contributors as $id => $tags) {
            $providerDef->addMethodCall('addContributor', [new Reference($id)]);
        }
    }
}

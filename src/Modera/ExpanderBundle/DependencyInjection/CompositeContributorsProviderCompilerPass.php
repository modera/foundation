<?php

namespace Modera\ExpanderBundle\DependencyInjection;

use Modera\ExpanderBundle\Ext\ChainMergeContributorsProvider;
use Modera\ExpanderBundle\Ext\ExtensionPoint;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @internal use \Modera\ExpanderBundle\Ext\ExtensionPoint::createCompilerPass() instead!
 *
 * The compiler pass will collect services from the constructor with a defined tag, and create a new service which may
 * be used later to get an aggregated value of their getItems method
 */
class CompositeContributorsProviderCompilerPass implements CompilerPassInterface, ExtensionPointAwareCompilerPassInterface
{
    private string $providerServiceId;

    private ?string $contributorServiceTagName;

    private ?ExtensionPoint $extensionPoint;

    /**
     * @param string  $providerServiceId         This compiler class will contribute a new service with this ID to the
     *                                           container, it will be an instance of the ChainMergeContributorsProvider class
     * @param ?string $contributorServiceTagName And the aforementioned instance will collect services from the
     *                                           container which were tagger with this ID
     */
    public function __construct(string $providerServiceId, ?string $contributorServiceTagName = null, ?ExtensionPoint $extensionPoint = null)
    {
        $this->providerServiceId = $providerServiceId;
        $this->contributorServiceTagName = $contributorServiceTagName ?: $providerServiceId;
        $this->extensionPoint = $extensionPoint;
    }

    public function getProviderServiceId(): string
    {
        return $this->providerServiceId;
    }

    public function getContributorServiceTagName(): ?string
    {
        return $this->contributorServiceTagName;
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

        if ($this->getContributorServiceTagName()) {
            $contributors = $container->findTaggedServiceIds($this->getContributorServiceTagName());
            foreach ($contributors as $id => $attributes) {
                $providerDef->addMethodCall('addContributor', [new Reference($id)]);
            }
        }
    }
}

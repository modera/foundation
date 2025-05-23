<?php

namespace Modera\DirectBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link https://symfony.com/doc/current/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('modera_direct');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                // all routes exposed will be prefixed with value of this configuration property,
                // this might prove useful when you want to secure all routes (place them behind
                // a configured firewall)
                // see Resources/config/routing.yaml
                ->scalarNode('routes_prefix')
                    ->defaultValue('')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}

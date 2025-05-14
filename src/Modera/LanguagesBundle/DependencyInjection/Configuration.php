<?php

namespace Modera\LanguagesBundle\DependencyInjection;

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
        $treeBuilder = new TreeBuilder('modera_languages');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->prototype('array')
                ->children()
                    ->scalarNode('locale')->end()
                    ->booleanNode('is_enabled')->defaultValue(true)->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}

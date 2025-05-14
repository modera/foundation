<?php

namespace Modera\MJRCacheAwareClassLoaderBundle\DependencyInjection;

use Modera\MJRCacheAwareClassLoaderBundle\VersionResolving\StandardVersionResolver;
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
        $treeBuilder = new TreeBuilder('modera_mjr_cache_aware_class_loader');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('version')->end()
                ->scalarNode('url')
                    ->defaultValue('extjs-class-loader.js')
                ->end()
                ->scalarNode('version_resolver')
                    ->defaultValue(StandardVersionResolver::class)
                ->end()
                ->arrayNode('listener_response_paths')
                    ->info('An array of URL REGEXPs where version number should be injected as X-Modera-Version header')
                    ->prototype('scalar')->end()
                ->end()
            ->end()
        ;

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }
}

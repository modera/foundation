<?php

namespace Modera\FileRepositoryBundle\DependencyInjection;

use Modera\FileRepositoryBundle\Intercepting\DefaultInterceptorsProvider;
use Modera\FileRepositoryBundle\UrlGeneration\UrlGenerator;
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
        $treeBuilder = new TreeBuilder('modera_file_repository');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                // This node add ability to control access to stored files through the proxy controller
                // See: \Modera\FileRepositoryBundle\Entity\StoredFile::getUrl
                ->arrayNode('controller')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('is_enabled')
                            ->defaultValue(true)
                        ->end()
                        ->scalarNode('route_url_prefix')
                            ->defaultValue('/u')
                        ->end()
                        // See: \Modera\FileRepositoryBundle\UrlGeneration\UrlGenerator
                        ->scalarNode('get_file_route')
                            ->defaultValue('modera_file_repository.get_file')
                        ->end()
                    ->end()
                ->end()
                // Must implement \Modera\FileRepositoryBundle\UrlGeneration\UrlGeneratorInterface
                ->scalarNode('default_url_generator')
                    ->defaultValue(UrlGenerator::class)
                ->end()
                ->arrayNode('url_generators')
                    ->prototype('variable')->end()
                ->end()
                // Should point to an implementation of \Modera\FileRepositoryBundle\Intercepting\InterceptorsProviderInterface
                ->scalarNode('interceptors_provider')
                    ->defaultValue(DefaultInterceptorsProvider::class)
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}

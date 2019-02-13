<?php

namespace Modera\MJRSecurityIntegrationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('modera_mjr_security_integration');

        $rootNode
            ->children()
                ->scalarNode('login_url')
                    ->cannotBeEmpty()
                    ->defaultValue('_security_check')
                ->end()
                ->scalarNode('logout_url')
                    ->cannotBeEmpty()
                    ->defaultValue('_security_logout')
                ->end()
                ->scalarNode('is_authenticated_url')
                    ->cannotBeEmpty()
                    ->defaultValue('modera_mjr_security_integration.index.is_authenticated')
                ->end()
                ->scalarNode('extjs_ajax_timeout')
                    ->cannotBeEmpty()
                    ->defaultValue(60000)
                ->end()
            ->end();

        return $treeBuilder;
    }
}

<?php

namespace Modera\MJRSecurityIntegrationBundle\DependencyInjection;

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
        $treeBuilder = new TreeBuilder('modera_mjr_security_integration');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('html_doctype_tag')
                    ->defaultValue('<!doctype html>')
                ->end()
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
                ->scalarNode('switch_user_list_action')
                    ->cannotBeEmpty()
                    ->defaultValue('Actions.ModeraMJRSecurityIntegration_SwitchUser.list')
                ->end()
            ->end();

        return $treeBuilder;
    }
}

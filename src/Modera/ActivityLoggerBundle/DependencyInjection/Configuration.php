<?php

namespace Modera\ActivityLoggerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @see ServiceAliasCompilerPass
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('modera_activity_logger');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                // must contain service container ID of an \Modera\ActivityLoggerBundle\Manager\ActivityManagerInterface
                // implementation.
                ->scalarNode('activity_manager')
                    ->cannotBeEmpty()
                    ->defaultValue('modera_activity_logger.manager.doctrine_orm_activity_manager')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}

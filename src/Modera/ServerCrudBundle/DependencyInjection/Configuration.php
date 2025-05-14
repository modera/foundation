<?php

namespace Modera\ServerCrudBundle\DependencyInjection;

use Modera\ServerCrudBundle\DataMapping\DefaultDataMapper;
use Modera\ServerCrudBundle\EntityFactory\DefaultEntityFactory;
use Modera\ServerCrudBundle\ExceptionHandling\BypassExceptionHandler;
use Modera\ServerCrudBundle\Hydration\HydrationService;
use Modera\ServerCrudBundle\NewValuesFactory\DefaultNewValuesFactory;
use Modera\ServerCrudBundle\Persistence\DefaultModelManager;
use Modera\ServerCrudBundle\Persistence\DoctrineRegistryPersistenceHandler;
use Modera\ServerCrudBundle\Validation\DefaultEntityValidator;
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
        $treeBuilder = new TreeBuilder('modera_server_crud');
        $rootNode = $treeBuilder->getRootNode();

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        $rootNode
            ->children()
                ->scalarNode('persistence_handler')
                    ->defaultValue(DoctrineRegistryPersistenceHandler::class)
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('model_manager')
                    ->defaultValue(DefaultModelManager::class)
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('entity_validator')
                    ->defaultValue(DefaultEntityValidator::class)
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('data_mapper')
                    ->defaultValue(DefaultDataMapper::class)
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('entity_factory')
                    ->defaultValue(DefaultEntityFactory::class)
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('exception_handler')
                    ->defaultValue(BypassExceptionHandler::class)
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('hydrator')
                    ->defaultValue(HydrationService::class)
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('new_values_factory')
                    ->defaultValue(DefaultNewValuesFactory::class)
                    ->cannotBeEmpty()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}

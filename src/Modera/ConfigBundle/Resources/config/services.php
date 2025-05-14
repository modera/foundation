<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Modera\ConfigBundle\Config\AsIsHandler;
use Modera\ConfigBundle\Config\BooleanHandler;
use Modera\ConfigBundle\Config\ConfigEntriesInstaller;
use Modera\ConfigBundle\Config\DictionaryHandler;
use Modera\ConfigBundle\Config\EntityRepositoryHandler;
use Modera\ConfigBundle\Entity\ConfigurationEntry;
use Modera\ConfigBundle\Listener\ConfigurationEntryEventListener;
use Modera\ConfigBundle\Listener\InitConfigurationEntry;
use Modera\ConfigBundle\Listener\OwnerRelationMappingListener;
use Modera\ConfigBundle\Manager\ConfigurationEntriesManager;
use Modera\ConfigBundle\Manager\ConfigurationEntriesManagerInterface;
use Modera\ConfigBundle\Manager\UniquityValidator;
use Modera\ConfigBundle\Notifying\NotificationCenter;
use Modera\ConfigBundle\Twig\TwigExtension;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->private()
            ->autowire()
            ->autoconfigure()
    ;

    $services->set(AsIsHandler::class)->public();
    $services->set(BooleanHandler::class)->public();
    $services->set(DictionaryHandler::class)->public();
    $services->set(EntityRepositoryHandler::class)->public();

    // TODO: remove, BC
    $services->alias('modera_config.as_is_handler', AsIsHandler::class)->public();
    $services->alias('modera_config.boolean_handler', BooleanHandler::class)->public();
    $services->alias('modera_config.dictionary_handler', DictionaryHandler::class)->public();
    $services->alias('modera_config.entity_repository_handler', EntityRepositoryHandler::class)->public();

    $services->set(ConfigEntriesInstaller::class);

    $services->set(ConfigurationEntryEventListener::class)
        ->tag('doctrine.orm.entity_listener', [
            'entity' => ConfigurationEntry::class,
            'event' => 'postPersist',
            'lazy' => true,
        ])
        ->tag('doctrine.orm.entity_listener', [
            'entity' => ConfigurationEntry::class,
            'event' => 'postUpdate',
            'lazy' => true,
        ])
        ->tag('doctrine.orm.entity_listener', [
            'entity' => ConfigurationEntry::class,
            'event' => 'postRemove',
            'lazy' => true,
        ])
    ;

    $services->set(InitConfigurationEntry::class)
        ->arg('$container', service('service_container'))
        ->tag('doctrine.orm.entity_listener', [
            'entity' => ConfigurationEntry::class,
            'event' => 'postLoad',
            'lazy' => true,
        ])
        ->tag('doctrine.orm.entity_listener', [
            'entity' => ConfigurationEntry::class,
            'event' => 'postPersist',
            'lazy' => true,
        ])
    ;

    $services->set(OwnerRelationMappingListener::class) // see ModeraConfigExtension
        ->arg('$semanticConfig', param('modera_config.config'))
    ;

    $services->set(ConfigurationEntriesManager::class)
        ->arg('$semanticConfig', param('modera_config.config'))
    ;
    $services->alias(ConfigurationEntriesManagerInterface::class, ConfigurationEntriesManager::class);

    $services->set(UniquityValidator::class)
        ->arg('$semanticConfig', param('modera_config.config'))
    ;

    $services->set(NotificationCenter::class);

    $services->set(TwigExtension::class)
        ->tag('twig.extension')
    ;
};

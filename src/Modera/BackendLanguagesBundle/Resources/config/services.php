<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Modera\BackendLanguagesBundle\Contributions\ClassLoaderMappingsProvider;
use Modera\BackendLanguagesBundle\Contributions\ClientDiServiceDefinitionsProvider;
use Modera\BackendLanguagesBundle\Contributions\ConfigMergersProvider;
use Modera\BackendLanguagesBundle\Contributions\JsResourcesProvider;
use Modera\BackendLanguagesBundle\Contributions\RoutingResourcesProvider;
use Modera\BackendLanguagesBundle\Contributions\SettingsSectionsProvider;
use Modera\BackendLanguagesBundle\EventListener\LocaleListener;
use Modera\BackendLanguagesBundle\EventListener\SettingsEntityManagingListener;
use Modera\BackendLanguagesBundle\Service\SanitizationService;
use Modera\BackendLanguagesBundle\Service\SanitizeInterface;
use Modera\BackendLanguagesBundle\Twig\Extension;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->private()
            ->autowire()
            ->autoconfigure()
    ;

    $services->set(ClassLoaderMappingsProvider::class);

    $services->set(ClientDiServiceDefinitionsProvider::class);

    $services->set(ConfigMergersProvider::class)
        ->arg('$locale', param('kernel.default_locale'))
    ;

    $services->set(JsResourcesProvider::class)
        ->arg('$defaultLocale', param('kernel.default_locale'))
    ;

    $services->set(RoutingResourcesProvider::class);

    $services->set(SettingsSectionsProvider::class);

    $services->set(LocaleListener::class)
        ->arg('$defaultLocale', param('kernel.default_locale'))
        ->arg('$isAuthenticatedRoute', param('modera_mjr_security_integration.config.is_authenticated_url'))
        ->tag('kernel.event_subscriber')
    ;

    $services->set(SettingsEntityManagingListener::class)
        ->tag('doctrine.event_listener', ['event' => 'onFlush'])
    ;

    $services->set(SanitizationService::class);
    $services->alias(SanitizeInterface::class, SanitizationService::class);

    $services->set(Extension::class)
        ->tag('twig.extension')
    ;
};

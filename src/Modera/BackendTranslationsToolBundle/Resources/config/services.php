<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Modera\BackendTranslationsToolBundle\Cache\CompileNeeded;
use Modera\BackendTranslationsToolBundle\Contributions\ConfigMergersProvider;
use Modera\BackendTranslationsToolBundle\Contributions\CssResourcesProvider;
use Modera\BackendTranslationsToolBundle\Contributions\FiltersProvider;
use Modera\BackendTranslationsToolBundle\Contributions\PermissionCategoriesProvider;
use Modera\BackendTranslationsToolBundle\Contributions\PermissionsProvider;
use Modera\BackendTranslationsToolBundle\Contributions\SectionsProvider;
use Modera\BackendTranslationsToolBundle\Contributions\ToolsSectionsProvider;
use Modera\BackendTranslationsToolBundle\Extractor\ExtjsExtractor;
use Modera\BackendTranslationsToolBundle\Handling\ExtjsTranslationHandler;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->private()
            ->autowire()
            ->autoconfigure()
    ;

    $services->set(CompileNeeded::class)
        ->arg('$cache', service('cache.app'))
    ;

    $services->set(ConfigMergersProvider::class);

    $services->set(CssResourcesProvider::class);

    $services->set(FiltersProvider::class)
        ->arg('$container', service('service_container'))
    ;

    $services->set(PermissionCategoriesProvider::class);

    $services->set(PermissionsProvider::class);

    $services->set(SectionsProvider::class);

    $services->set(ToolsSectionsProvider::class);

    $services->set(ExtjsExtractor::class);

    $services->set(ExtjsTranslationHandler::class)
        ->abstract()
        ->autoconfigure(false)
        ->arg('$extractor', service(ExtjsExtractor::class))
    ;

    // TODO: remove, BC
    $services->set('modera_backend_translations_tool.handling.extjs_translation_handler', ExtjsTranslationHandler::class)
        ->abstract()
        ->autowire(false)
        ->autoconfigure(false)
        ->args([
            service(ExtjsExtractor::class),
        ])
    ;
};

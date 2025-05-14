<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Modera\MJRCacheAwareClassLoaderBundle\Contributions\JsResourcesProvider;
use Modera\MJRCacheAwareClassLoaderBundle\Contributions\RoutingResourcesProvider;
use Modera\MJRCacheAwareClassLoaderBundle\EventListener\VersionInjectorEventListener;
use Modera\MJRCacheAwareClassLoaderBundle\VersionResolving\StandardVersionResolver;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->private()
            ->autowire()
            ->autoconfigure()
    ;

    $services->set(JsResourcesProvider::class);

    $services->set(RoutingResourcesProvider::class);

    $services->set(VersionInjectorEventListener::class)
        ->arg('$semanticConfig', param('modera_mjr_cache_aware_class_loader.config'))
        ->tag('kernel.event_listener', [
            'event' => 'kernel.response',
            'method' => 'onKernelResponse',
        ])
    ;

    $services->set(StandardVersionResolver::class)
        ->arg('$container', service('service_container'))
    ;
};

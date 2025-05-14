<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Modera\RoutingBundle\Routing\Loader;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->private()
            ->autowire()
            ->autoconfigure()
    ;

    $services->set(Loader::class)
        ->arg('$resourcesProvider', service('modera_routing.routing_resources_provider'))
        ->arg('$rootLoader', service('modera_routing.symfony_delegating_loader'))
        ->tag('routing.loader')
    ;
};

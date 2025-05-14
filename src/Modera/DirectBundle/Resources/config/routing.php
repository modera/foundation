<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Modera\DirectBundle\Contributions\RoutingResourcesProvider;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->private()
            ->autowire()
            ->autoconfigure()
    ;

    $services->set(RoutingResourcesProvider::class);
};

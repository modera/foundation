<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->private()
            ->autowire()
            ->autoconfigure()
    ;

    $services
        ->load(
            'Modera\\FileRepositoryBundle\\Controller\\',
            '../../Controller/*',
        )
    ;
};

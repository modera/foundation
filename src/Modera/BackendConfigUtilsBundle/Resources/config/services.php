<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Modera\BackendConfigUtilsBundle\Contributions\ClassLoaderMappingsProvider;
use Modera\BackendConfigUtilsBundle\Contributions\PermissionsProvider;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->private()
            ->autowire()
            ->autoconfigure()
    ;

    $services->set(ClassLoaderMappingsProvider::class);

    $services->set(PermissionsProvider::class);
};

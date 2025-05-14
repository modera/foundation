<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Modera\BackendToolsBundle\Contributions\CssResourcesProvider;
use Modera\BackendToolsBundle\Contributions\MenuItemsProvider;
use Modera\BackendToolsBundle\Contributions\PermissionsProvider;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->private()
            ->autowire()
            ->autoconfigure()
    ;

    $services->set(CssResourcesProvider::class);

    $services->set(MenuItemsProvider::class)
        ->arg('$tabOrder', param('modera_backend_tools.tab_order'))
    ;

    $services->set(PermissionsProvider::class);
};

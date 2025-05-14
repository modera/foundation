<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Modera\BackendSecurityBundle\Contributions\ClientDiServiceDefinitionsProvider;
use Modera\BackendSecurityBundle\Contributions\ConfigMergersProvider;
use Modera\BackendSecurityBundle\Contributions\CssResourcesProvider;
use Modera\BackendSecurityBundle\Contributions\PermissionCategoriesProvider;
use Modera\BackendSecurityBundle\Contributions\PermissionsProvider;
use Modera\BackendSecurityBundle\Contributions\SectionsProvider;
use Modera\BackendSecurityBundle\Contributions\ServiceDefinitionsProvider;
use Modera\BackendSecurityBundle\Contributions\ToolsSectionsProvider;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->private()
            ->autowire()
            ->autoconfigure()
    ;

    $services->set(ClientDiServiceDefinitionsProvider::class);

    $services->set(ConfigMergersProvider::class)
        ->arg('$semanticConfig', param('modera_backend_security.config'))
    ;

    $services->set(CssResourcesProvider::class);

    $services->set(PermissionCategoriesProvider::class);

    $services->set(PermissionsProvider::class);

    $services->set(SectionsProvider::class);

    $services->set(ServiceDefinitionsProvider::class);

    $services->set(ToolsSectionsProvider::class);
};

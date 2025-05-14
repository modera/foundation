<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Modera\MJRSecurityIntegrationBundle\Contributions\ClassLoaderMappingsProvider;
use Modera\MJRSecurityIntegrationBundle\Contributions\ClientDiServiceDefinitionsProvider;
use Modera\MJRSecurityIntegrationBundle\Contributions\ConfigMergersProvider;
use Modera\MJRSecurityIntegrationBundle\Contributions\PermissionCategoriesProvider;
use Modera\MJRSecurityIntegrationBundle\Contributions\PermissionsProvider;
use Modera\MJRSecurityIntegrationBundle\Contributions\RoutingResourcesProvider;
use Modera\MJRSecurityIntegrationBundle\Contributions\ServiceDefinitionsProvider;
use Modera\MJRSecurityIntegrationBundle\DependencyInjection\ModeraMJRSecurityIntegrationExtension;
use Modera\MJRSecurityIntegrationBundle\EventListener\AjaxAuthenticationValidatingListener;
use Modera\SecurityBundle\DependencyInjection\ModeraSecurityExtension;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->private()
            ->autowire()
            ->autoconfigure()
    ;

    $services->set(ClassLoaderMappingsProvider::class);

    $services->set(ClientDiServiceDefinitionsProvider::class)
        ->arg('$securityConfig', param('modera_security.config'))
    ;

    $services->set(ConfigMergersProvider::class)
        ->arg('$clientDiDefinitionsProvider', service('modera_mjr_security_integration.client_di_service_defs_provider'))
        ->arg('$bundleConfig', param('modera_mjr_security_integration.config'))
        ->arg('$securityConfig', param('modera_security.config'))
        ->arg('$roleHierarchy', param('security.role_hierarchy.roles'))
    ;

    $services->set(PermissionCategoriesProvider::class);

    $services->set(PermissionsProvider::class);

    $services->set(RoutingResourcesProvider::class);

    $services->set(ServiceDefinitionsProvider::class)
        ->arg('$bundleConfig', param(ModeraMJRSecurityIntegrationExtension::CONFIG_KEY))
        ->arg('$switchUserConfig', param(ModeraSecurityExtension::CONFIG_KEY.'.switch_user'))
    ;

    $services->set(AjaxAuthenticationValidatingListener::class)
        ->arg('$backendRoutesPrefix', param('modera_mjr_integration.routes_prefix'))
        ->tag('kernel.event_listener', [
            'event' => 'kernel.exception',
            'priority' => 1000,
        ])
    ;
};

<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Modera\MjrIntegrationBundle\AssetsHandling\AssetsProvider;
use Modera\MjrIntegrationBundle\AssetsHandling\AssetsProviderInterface;
use Modera\MjrIntegrationBundle\ClientSideDependencyInjection\ServiceDefinitionsManager;
use Modera\MjrIntegrationBundle\Config\BundleSemanticMainConfig;
use Modera\MjrIntegrationBundle\Config\ConfigManager;
use Modera\MjrIntegrationBundle\Contributions\ClassLoaderMappingsProvider;
use Modera\MjrIntegrationBundle\Contributions\ClientDiServiceDefinitionsProvider;
use Modera\MjrIntegrationBundle\Contributions\Config\StandardConfigMergersProvider;
use Modera\MjrIntegrationBundle\Contributions\CssResourcesProvider;
use Modera\MjrIntegrationBundle\Contributions\JsResourcesProvider;
use Modera\MjrIntegrationBundle\Contributions\RoutingResourcesProvider;
use Modera\MjrIntegrationBundle\Contributions\ServiceDefinitionsProvider;
use Modera\MjrIntegrationBundle\Contributions\SteroidClassMappingsProvider;
use Modera\MjrIntegrationBundle\DependencyInjection\ModeraMjrIntegrationExtension;
use Modera\MjrIntegrationBundle\Menu\MenuManager;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->private()
            ->autowire()
            ->autoconfigure()
    ;

    $services->set(AssetsProvider::class);
    $services->alias(AssetsProviderInterface::class, AssetsProvider::class);
    // TODO: remove, BC
    $services->alias('modera_mjr_integration.assets_handling.assets_provider', AssetsProviderInterface::class);

    $services->set(ServiceDefinitionsManager::class);

    $services->set(BundleSemanticMainConfig::class)
        ->arg('$config', param(ModeraMjrIntegrationExtension::CONFIG_KEY))
    ;

    $services->set(ConfigManager::class);

    $services->set(StandardConfigMergersProvider::class);

    $services->set(ClassLoaderMappingsProvider::class);

    $services->set(ClientDiServiceDefinitionsProvider::class);

    $services->set(CssResourcesProvider::class);

    $services->set(JsResourcesProvider::class)
        ->arg('$bundleConfig', param(ModeraMjrIntegrationExtension::CONFIG_KEY))
        ->arg('$kernelEnvironment', param('kernel.environment'))
    ;

    $services->set(RoutingResourcesProvider::class);

    $services->set(ServiceDefinitionsProvider::class)
        ->arg('$bundleConfig', param(ModeraMjrIntegrationExtension::CONFIG_KEY))
    ;

    $services->set(SteroidClassMappingsProvider::class);

    $services->set(MenuManager::class);
};

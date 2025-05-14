<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Modera\DynamicallyConfigurableMJRBundle\Contributions\ClassLoaderMappingsProvider;
use Modera\DynamicallyConfigurableMJRBundle\Contributions\ConfigEntriesProvider;
use Modera\DynamicallyConfigurableMJRBundle\Contributions\ConfigMergersProvider;
use Modera\DynamicallyConfigurableMJRBundle\Contributions\CssResourcesProvider;
use Modera\DynamicallyConfigurableMJRBundle\Contributions\JsResourcesProvider;
use Modera\DynamicallyConfigurableMJRBundle\Contributions\SettingsSectionsProvider;
use Modera\DynamicallyConfigurableMJRBundle\MJR\MainConfig;
use Modera\DynamicallyConfigurableMJRBundle\Resolver\ValueResolver;
use Modera\DynamicallyConfigurableMJRBundle\Resolver\ValueResolverInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->private()
            ->autowire()
            ->autoconfigure()
    ;

    $services->set(ClassLoaderMappingsProvider::class);

    $services->set(ConfigEntriesProvider::class);

    $services->set(ConfigMergersProvider::class);

    $services->set(CssResourcesProvider::class);

    $services->set(JsResourcesProvider::class);

    $services->set(SettingsSectionsProvider::class);

    $services->set(MainConfig::class);

    $services->set(ValueResolver::class);
    $services->alias(ValueResolverInterface::class, ValueResolver::class);
};

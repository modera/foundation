<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Modera\MjrIntegrationBundle\DependencyInjection\ModeraMjrIntegrationExtension;
use Modera\MJRThemeIntegrationBundle\Contributions\CssResourcesProvider;
use Modera\MJRThemeIntegrationBundle\Contributions\JsResourcesProvider;
use Modera\MJRThemeIntegrationBundle\DependencyInjection\ModeraMJRThemeIntegrationExtension;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->private()
            ->autowire()
            ->autoconfigure()
    ;

    $services->set(CssResourcesProvider::class)
        ->arg('$themeIntegrationConfig', param(ModeraMJRThemeIntegrationExtension::CONFIG_KEY))
        ->arg('$mjrIntegrationConfig', param(ModeraMjrIntegrationExtension::CONFIG_KEY))
        ->arg('$kernelEnvironment', param('kernel.environment'))
    ;

    $services->set(JsResourcesProvider::class)
        ->arg('$themeIntegrationConfig', param(ModeraMJRThemeIntegrationExtension::CONFIG_KEY))
    ;
};

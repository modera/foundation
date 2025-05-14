<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Modera\BackendToolsSettingsBundle\Contributions\ConfigMergersProvider;
use Modera\BackendToolsSettingsBundle\Contributions\CssResourcesProvider;
use Modera\BackendToolsSettingsBundle\Contributions\SectionsConfigMerger;
use Modera\BackendToolsSettingsBundle\Contributions\SectionsProvider;
use Modera\BackendToolsSettingsBundle\Contributions\ToolsSectionsProvider;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->private()
            ->autowire()
            ->autoconfigure()
    ;

    $services->set(ConfigMergersProvider::class);

    $services->set(CssResourcesProvider::class);

    $services->set(SectionsConfigMerger::class);

    $services->set(SectionsProvider::class);

    $services->set(ToolsSectionsProvider::class);
};

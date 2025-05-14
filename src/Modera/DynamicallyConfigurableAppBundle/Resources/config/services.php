<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Modera\DynamicallyConfigurableAppBundle\Contributions\ConfigEntriesProvider;
use Modera\DynamicallyConfigurableAppBundle\ValueHandling\KernelConfigWriter;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->private()
            ->autowire()
            ->autoconfigure()
    ;

    $services->set(ConfigEntriesProvider::class);

    $services->set(KernelConfigWriter::class)
        ->public()
        ->arg('$kernelConfigFQCN', param('modera_dynamically_configurable_app.kernel_config_fqcn'))
    ;
    // TODO: remove, BC
    $services->alias('modera_dynamically_configurable_app.value_handling.kernel_config_writer', KernelConfigWriter::class)->public();
};

<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Modera\ExpanderBundle\Ext\ExtensionPointManager;
use Modera\ExpanderBundle\Ext\ExtensionProvider;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->private()
            ->autowire()
            ->autoconfigure()
            ->bind('$container', service('service_container'))
            ->bind('$extensionPoints', param('modera_expander.extension_points'))
    ;

    $services->set(ExtensionPointManager::class);

    $services->set(ExtensionProvider::class);
};

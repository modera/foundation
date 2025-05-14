<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->private()
            ->autowire()
            ->autoconfigure()
            ->bind('$extensionPoints', param('modera_expander.extension_points'))
    ;

    $services
        ->load(
            'Modera\\ExpanderBundle\\Command\\',
            '../../Command/*',
        )
        ->exclude([
            '../../Command/AbstractCommand.php',
        ])
    ;
};

<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Modera\ActivityLoggerBundle\Manager\DoctrineOrmActivityManager;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->private()
            ->autowire()
            ->autoconfigure()
    ;

    $services->set(DoctrineOrmActivityManager::class);
};

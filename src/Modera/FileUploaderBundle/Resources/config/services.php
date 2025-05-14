<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Modera\FileUploaderBundle\Contributions\RoutingResourcesProvider;
use Modera\FileUploaderBundle\Uploading\WebUploader;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->private()
            ->autowire()
            ->autoconfigure()
    ;

    $services->set(RoutingResourcesProvider::class);

    $services->set(WebUploader::class);
};

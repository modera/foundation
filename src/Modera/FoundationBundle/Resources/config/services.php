<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Modera\FoundationBundle\Twig\Extension;

return static function (ContainerConfigurator $container): void {
    $container->parameters()
        ->set('modera_foundation.public_dir', param('kernel.project_dir').'/public')
    ;

    $services = $container->services()
        ->defaults()
            ->private()
            ->autowire()
            ->autoconfigure()
    ;

    $services->set(Extension::class)
        ->arg('$publicDir', param('modera_foundation.public_dir'))
        ->tag('twig.extension')
    ;
};

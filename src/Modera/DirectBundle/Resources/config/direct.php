<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Modera\DirectBundle\Api\ApiFactory;
use Modera\DirectBundle\Controller\DirectController;
use Modera\DirectBundle\Router\RouterFactory;
use Modera\DirectBundle\Router\RouterFactoryInterface;

return static function (ContainerConfigurator $container): void {
    $container->parameters()
        ->set('direct.api.route_pattern', param('modera_direct.routes_prefix').'/route')
        ->set('direct.api.enable_buffer', false)
        ->set('direct.api.type', 'remoting')
        ->set('direct.api.namespace', 'Actions')
        ->set('direct.api.id', 'API')
        ->set('direct.api.remote_attribute', '@Remote')
        ->set('direct.api.form_attribute', '@Form')
        ->set('direct.api.safe_attribute', '@Secure')
        ->set('direct.api.unsafe_attribute', '@Anonymous')
        ->set('direct.api.default_access', 'anonymous')
        ->set('direct.api.session_attribute', 'account')
        ->set('direct.exception.message', 'Whoops, looks like something went wrong.')
    ;

    $services = $container->services()
        ->defaults()
            ->private()
            ->autowire()
            ->autoconfigure()
    ;

    $services->set(ApiFactory::class)
        ->arg('$container', service('service_container'))
    ;

    $services->set(DirectController::class);

    $services->set(RouterFactory::class)
        ->arg('$container', service('service_container'))
    ;
    $services->alias(RouterFactoryInterface::class, RouterFactory::class);
};

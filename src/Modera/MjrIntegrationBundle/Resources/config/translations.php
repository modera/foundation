<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Modera\BackendTranslationsToolBundle\Handling\ExtjsTranslationHandler;

return static function (ContainerConfigurator $container): void {
    $container->parameters()
        ->set('modera_mjr_integration.public_dir', param('kernel.project_dir').'/public')
    ;

    $services = $container->services()
        ->defaults()
            ->private()
            ->autowire()
            ->autoconfigure()
    ;

    $services->set(null, ExtjsTranslationHandler::class)
        ->arg('$bundle', 'ModeraMjrIntegrationBundle')
        ->call('setResourcesDirectory', [
            '$resourcesDirectory' => param('modera_mjr_integration.public_dir').param('modera_mjr_integration.config.runtime_path'),
        ])
    ;
};

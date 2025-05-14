<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Modera\BackendTranslationsToolBundle\Handling\ExtjsTranslationHandler;
use Modera\TranslationsBundle\Handling\PhpClassesTranslationHandler;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->private()
            ->autowire()
            ->autoconfigure()
    ;

    $services->set(null, ExtjsTranslationHandler::class)
        ->arg('$bundle', 'ModeraBackendSecurityBundle')
    ;

    $services->set(null, PhpClassesTranslationHandler::class)
        ->arg('$bundle', 'ModeraBackendSecurityBundle')
    ;
};

<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Modera\TranslationsBundle\Handling\PhpClassesTranslationHandler;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->private()
            ->autowire()
            ->autoconfigure()
    ;

    $services->set(null, PhpClassesTranslationHandler::class)
        ->arg('$bundle', 'ModeraBackendConfigUtilsBundle')
    ;
};

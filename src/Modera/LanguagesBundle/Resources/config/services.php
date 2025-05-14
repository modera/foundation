<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Modera\LanguagesBundle\EventListener\LanguageSubscriber;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->private()
            ->autowire()
            ->autoconfigure()
    ;

    $services->set(LanguageSubscriber::class)
        ->tag('doctrine.event_subscriber')
    ;
};

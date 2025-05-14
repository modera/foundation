<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Modera\BackendSecurityBundle\PasswordStrength\Mail\DefaultMailService;
use Modera\SecurityBundle\PasswordStrength\Mail\MailServiceInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->private()
            ->autowire()
            ->autoconfigure()
    ;

    $services->set(DefaultMailService::class)
        ->arg('$defaultLocale', param('kernel.default_locale'))
        ->arg('$mailSender', param('modera_backend_security.config.mail_sender'))
    ;
    $services->alias(MailServiceInterface::class, DefaultMailService::class);
};

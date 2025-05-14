<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Modera\SecurityBundle\Contributions\RoutingResourcesProvider;
use Modera\SecurityBundle\DataInstallation\PermissionAndCategoriesInstaller;
use Modera\SecurityBundle\DependencyInjection\ModeraSecurityExtension;
use Modera\SecurityBundle\Entity\User;
use Modera\SecurityBundle\EventListener\AuthenticationSubscriber;
use Modera\SecurityBundle\EventListener\RootUserHandlerInjectionListener;
use Modera\SecurityBundle\EventListener\SwitchUserSubscriber;
use Modera\SecurityBundle\PasswordStrength\Mail\DefaultMailService;
use Modera\SecurityBundle\PasswordStrength\PasswordConfigInterface;
use Modera\SecurityBundle\PasswordStrength\PasswordManager;
use Modera\SecurityBundle\PasswordStrength\SemanticPasswordConfig;
use Modera\SecurityBundle\PasswordStrength\StrongPasswordValidator;
use Modera\SecurityBundle\RootUserHandling\SemanticConfigRootUserHandler;
use Modera\SecurityBundle\Security\Authenticator;
use Modera\SecurityBundle\Security\UserChecker;
use Modera\SecurityBundle\Service\UserService;
use Modera\SecurityBundle\Validator\Constraints\EmailValidator;
use Modera\SecurityBundle\Validator\Constraints\UsernameValidator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->private()
            ->autowire()
            ->autoconfigure()
    ;

    $services->set(RoutingResourcesProvider::class);

    $services->set(PermissionAndCategoriesInstaller::class)
        ->arg('$permissionCategoriesProvider', service('modera_security.permission_categories_provider'))
        ->arg('$permissionsProvider', service('modera_security.permissions_provider'))
        ->arg('$sortingPosition', param('modera_security.sorting_position'))
    ;

    $services->set(AuthenticationSubscriber::class)
        ->tag('kernel.event_subscriber')
    ;

    $services->set(RootUserHandlerInjectionListener::class)
        ->tag('doctrine.orm.entity_listener', [
            'entity' => User::class,
            'event' => 'postLoad',
            'lazy' => true,
        ])
    ;

    $services->set(SwitchUserSubscriber::class)
        ->arg('$bundleConfig', param('modera_security.config'))
        ->tag('kernel.event_subscriber')
    ;

    $services->set(DefaultMailService::class);

    $services->set(PasswordManager::class);

    $services->set(SemanticPasswordConfig::class)
        ->arg('$bundleSemanticConfig', param('modera_security.config'))
    ;
    $services->alias(PasswordConfigInterface::class, SemanticPasswordConfig::class);

    $services->set(StrongPasswordValidator::class)
        ->tag('validator.constraint_validator')
    ;

    $services->set(SemanticConfigRootUserHandler::class)
        ->arg('$bundleConfig', param(ModeraSecurityExtension::CONFIG_KEY))
    ;

    $services->set(Authenticator::class)
        ->tag('monolog.logger', [
            'channel' => 'security',
        ])
    ;

    $services->set(UserChecker::class);

    $services->set(UserService::class);

    $services->set(EmailValidator::class)
        ->tag('validator.constraint_validator', [
            'alias' => 'modera_security.validator.email',
        ])
    ;

    $services->set(UsernameValidator::class)
        ->tag('validator.constraint_validator', [
            'alias' => 'modera_security.validator.username',
        ])
    ;
};

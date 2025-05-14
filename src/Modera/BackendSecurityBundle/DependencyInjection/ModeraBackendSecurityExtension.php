<?php

namespace Modera\BackendSecurityBundle\DependencyInjection;

use Modera\BackendSecurityBundle\PasswordStrength\Mail\DefaultMailService;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Mailer\MailerInterface;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link https://symfony.com/doc/current/bundles/extension.html}
 */
class ModeraBackendSecurityExtension extends Extension implements PrependExtensionInterface
{
    public const CONFIG_KEY = 'modera_backend_security.config';

    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $this->injectConfigIntoContainer($config, $container);

        $loader = new Loader\PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('controller.php');
        $loader->load('services.php');
        $loader->load('translations.php');

        if (\interface_exists(MailerInterface::class)) {
            $loader->load('mail_service.php');
        }
    }

    /**
     * @param array<string, array<mixed>|bool|string|int|float|null> $config
     */
    private function injectConfigIntoContainer(array $config, ContainerBuilder $container): void
    {
        $container->setParameter(self::CONFIG_KEY, $config);
        foreach ($config as $key => $value) {
            $container->setParameter(self::CONFIG_KEY.'.'.$key, $value);
        }
    }

    public function prepend(ContainerBuilder $container): void
    {
        if (\interface_exists(MailerInterface::class)) {
            $container->prependExtensionConfig('modera_security', [
                'password_strength' => [
                    'mail' => [
                        'service' => DefaultMailService::class,
                    ],
                ],
            ]);
        }
    }
}

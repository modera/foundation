<?php

namespace Modera\BackendSecurityBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class ModeraBackendSecurityExtension extends Extension implements PrependExtensionInterface
{
    const CONFIG_KEY = 'modera_backend_security.config';

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $this->injectConfigIntoContainer($config, $container);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('controller.xml');
        $loader->load('services.xml');

        if (\interface_exists('Symfony\Component\Mailer\MailerInterface')) {
            $loader->load('mail_service.xml');
        }
    }

    private function injectConfigIntoContainer(array $config, ContainerBuilder $container)
    {
        $container->setParameter(self::CONFIG_KEY, $config);
        foreach ($config as $key => $value) {
            $container->setParameter(self::CONFIG_KEY.'.'.$key, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        if (\interface_exists('Symfony\Component\Mailer\MailerInterface')) {
            $container->prependExtensionConfig('modera_security', array(
                'password_strength' => array(
                    'mail' => array(
                        'service' => 'modera_backend_security.password_strength.mail.default_mail_service',
                    ),
                ),
            ));
        }
    }
}

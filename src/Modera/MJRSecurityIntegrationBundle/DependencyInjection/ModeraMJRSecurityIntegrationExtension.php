<?php

namespace Modera\MJRSecurityIntegrationBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class ModeraMJRSecurityIntegrationExtension extends Extension implements PrependExtensionInterface
{
    public const CONFIG_KEY = 'modera_mjr_security_integration.config';

    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if (0 === \count($config)) {
            throw new \RuntimeException('Bundle "ModeraMJRSecurityIntegrationBundle" must be configured in config.yml!');
        }

        $container->setParameter(self::CONFIG_KEY, $config);
        foreach ($config as $key => $value) {
            $container->setParameter(self::CONFIG_KEY.'.'.$key, $value);
        }

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('controller.xml');
        $loader->load('services.xml');

        if (\interface_exists('Modera\TranslationsBundle\Handling\TranslationHandlerInterface')) {
            $loader->load('translations.xml');
        }

        if (\class_exists('Modera\BackendTranslationsToolBundle\Handling\ExtjsTranslationHandler')) {
            $loader->load('extjs_translations.xml');
        }
    }

    public function prepend(ContainerBuilder $container): void
    {
        // Secured MJR application relies on AuthenticationRequiredApplication to bootstrap itself
        $container->prependExtensionConfig('modera_mjr_integration', [
            'app_base_class' => 'MF.runtime.applications.authenticationaware.AuthenticationRequiredApplication',
        ]);
    }
}

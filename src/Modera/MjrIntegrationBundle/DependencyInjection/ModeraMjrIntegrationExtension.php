<?php

namespace Modera\MjrIntegrationBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class ModeraMjrIntegrationExtension extends Extension
{
    public const CONFIG_KEY = 'modera_mjr_integration.config';
    public const CONFIG_APP_NAME = 'modera_mjr_integration.config.app_name';
    public const CONFIG_RUNTIME_PATH = 'modera_mjr_integration.config.runtime_path';
    public const CONFIG_ROUTES_PREFIX = 'modera_mjr_integration.routes_prefix';

    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter(self::CONFIG_KEY, $config);
        $container->setParameter(self::CONFIG_APP_NAME, $config['app_name']);
        $container->setParameter(self::CONFIG_RUNTIME_PATH, $config['runtime_path']);
        $container->setParameter(self::CONFIG_ROUTES_PREFIX, $config['routes_prefix']);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('controller.xml');
        $loader->load('services.xml');

        if (\class_exists('Modera\BackendTranslationsToolBundle\Handling\ExtjsTranslationHandler')) {
            try {
                $loader->load('translations.xml');
            } catch (\Exception $e) {
            }
        }

        if (\class_exists('Symfony\Component\Console\Application')) {
            try {
                $loader->load('console.xml');
            } catch (\Exception $e) {
            }
        }
    }
}

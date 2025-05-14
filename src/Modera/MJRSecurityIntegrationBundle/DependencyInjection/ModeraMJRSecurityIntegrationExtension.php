<?php

namespace Modera\MJRSecurityIntegrationBundle\DependencyInjection;

use Modera\BackendTranslationsToolBundle\Handling\ExtjsTranslationHandler;
use Modera\TranslationsBundle\Handling\TranslationHandlerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link https://symfony.com/doc/current/bundles/extension.html}
 */
class ModeraMJRSecurityIntegrationExtension extends Extension implements PrependExtensionInterface
{
    public const CONFIG_KEY = 'modera_mjr_security_integration.config';

    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if (0 === \count($config)) {
            throw new \RuntimeException('Bundle "ModeraMJRSecurityIntegrationBundle" must be configured in config.yaml!');
        }

        $container->setParameter(self::CONFIG_KEY, $config);
        foreach ($config as $key => $value) {
            $container->setParameter(self::CONFIG_KEY.'.'.$key, $value);
        }

        $loader = new Loader\PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('controller.php');
        $loader->load('services.php');

        if (\interface_exists(TranslationHandlerInterface::class)) {
            $loader->load('translations.php');
        }

        if (\class_exists(ExtjsTranslationHandler::class)) {
            $loader->load('extjs_translations.php');
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

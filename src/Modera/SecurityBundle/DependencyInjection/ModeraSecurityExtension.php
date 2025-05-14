<?php

namespace Modera\SecurityBundle\DependencyInjection;

use Modera\SecurityBundle\RootUserHandling\RootUserHandlerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link https://symfony.com/doc/current/bundles/extension.html}
 */
class ModeraSecurityExtension extends Extension implements PrependExtensionInterface
{
    public const CONFIG_KEY = 'modera_security.config';

    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('controller.php');
        $loader->load('services.php');
        $loader->load('translations.php');

        $config = $this->prependSwitchUserConfig($config);

        $this->injectConfigIntoContainer($config, $container);

        /** @var array{'root_user_handler': string} $config */
        $config = $config;

        $container
            ->setAlias(RootUserHandlerInterface::class, $config['root_user_handler'])
        ;

        if (\class_exists(Application::class)) {
            try {
                $loader->load('console.php');
            } catch (\Exception $e) {
            }
        }
    }

    /**
     * @param array<string, mixed> $config
     */
    private function injectConfigIntoContainer(array $config, ContainerBuilder $container): void
    {
        $container->setParameter(self::CONFIG_KEY, $config);

        /** @var array{'sorting_position': array<string, mixed>} $config */
        $config = $config;

        $container->setParameter(
            'modera_security.sorting_position',
            $config['sorting_position']
        );
    }

    public function prepend(ContainerBuilder $container): void
    {
        $configs = $container->getExtensionConfig($this->getAlias());

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        /** @var array{'firewalls': array<string, mixed>, 'switch_user': array{string, string}, 'access_control': array<mixed>} $config */
        $config = $this->prependSwitchUserConfig($config);

        if ($container->hasParameter('modera_security.default_firewalls')) {
            /** @var array<string, mixed> $defaultFirewalls */
            $defaultFirewalls = $container->getParameter('modera_security.default_firewalls');
            $config['firewalls'] = \array_merge(
                $config['firewalls'],
                $defaultFirewalls
            );
        }

        $container->setParameter(self::CONFIG_KEY.'.switch_user', $config['switch_user']);
        $container->setParameter(self::CONFIG_KEY.'.firewalls', $config['firewalls']);
        $container->setParameter(self::CONFIG_KEY.'.access_control', $config['access_control']);
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return array<string, mixed>
     */
    private function prependSwitchUserConfig(array $config): array
    {
        if (isset($config['switch_user']) && $config['switch_user']) {
            $switchUserDefaultCfg = [
                'role' => 'ROLE_ALLOWED_TO_SWITCH',
                'parameter' => '_switch_user',
            ];

            if (!\is_array($config['switch_user'])) {
                $config['switch_user'] = $switchUserDefaultCfg;
            }

            if (!isset($config['switch_user']['role'])) {
                $config['switch_user']['role'] = $switchUserDefaultCfg['role'];
            }

            if (!isset($config['switch_user']['parameter'])) {
                $config['switch_user']['parameter'] = $switchUserDefaultCfg['parameter'];
            }
        }

        return $config;
    }
}

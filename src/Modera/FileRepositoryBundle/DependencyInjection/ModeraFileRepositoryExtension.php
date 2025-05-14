<?php

namespace Modera\FileRepositoryBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link https://symfony.com/doc/current/bundles/extension.html}
 */
class ModeraFileRepositoryExtension extends Extension
{
    public const CONFIG_KEY = 'modera_file_repository.config';

    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('controller.php');
        $loader->load('services.php');

        $container->setParameter(self::CONFIG_KEY, $config);
        foreach ($config as $key => $value) {
            $container->setParameter(self::CONFIG_KEY.'.'.$key, $value);

            if ('controller' === $key) {
                foreach ($value as $k => $v) {
                    $container->setParameter(self::CONFIG_KEY.'.'.$key.'.'.$k, $v);
                }
            }
        }

        $container->setDefinition(
            'modera_file_repository.intercepting.interceptors_provider',
            $container->getDefinition($config['interceptors_provider'])
        )->setPublic(true);

        if (\class_exists(Application::class)) {
            try {
                $loader->load('console.php');
            } catch (\Exception $e) {
            }
        }
    }
}

<?php

namespace Modera\MJRCacheAwareClassLoaderBundle\DependencyInjection;

use Modera\MJRCacheAwareClassLoaderBundle\VersionResolving\VersionResolverInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link https://symfony.com/doc/current/bundles/extension.html}
 */
class ModeraMJRCacheAwareClassLoaderExtension extends Extension
{
    public const CONFIG_KEY = 'modera_mjr_cache_aware_class_loader.config';

    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('controller.php');
        $loader->load('services.php');

        $container->setParameter(self::CONFIG_KEY, $config);

        $container->setParameter('modera_mjr_cache_aware_class_loader.route', $config['url']);

        $container
            ->setAlias(VersionResolverInterface::class, $config['version_resolver'])
        ;
    }
}

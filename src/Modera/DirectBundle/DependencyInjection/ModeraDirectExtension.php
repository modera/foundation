<?php

namespace Modera\DirectBundle\DependencyInjection;

use Modera\ExpanderBundle\Ext\ContributorInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link https://symfony.com/doc/current/bundles/extension.html}
 */
class ModeraDirectExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('modera_direct.routes_prefix', $config['routes_prefix']);

        $loader = new Loader\PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('direct.php');

        if (\interface_exists(ContributorInterface::class)) {
            try {
                $loader->load('routing.php');
            } catch (\Exception $e) {
            }
        }
    }
}

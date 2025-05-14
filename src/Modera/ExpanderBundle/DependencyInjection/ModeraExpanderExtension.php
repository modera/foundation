<?php

namespace Modera\ExpanderBundle\DependencyInjection;

use Modera\ExpanderBundle\Ext\AsContributorFor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link https://symfony.com/doc/current/bundles/extension.html}
 */
class ModeraExpanderExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.php');

        if (\class_exists(Application::class)) {
            try {
                $loader->load('console.php');
            } catch (\Exception $e) {
            }
        }

        $container->registerAttributeForAutoconfiguration(AsContributorFor::class, static function (ChildDefinition $definition, AsContributorFor $attribute): void {
            $definition->addTag('modera_expander.contributor');
        });
    }
}

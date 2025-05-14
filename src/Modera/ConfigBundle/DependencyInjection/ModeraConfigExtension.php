<?php

namespace Modera\ConfigBundle\DependencyInjection;

use Modera\ConfigBundle\Listener\OwnerRelationMappingListener;
use Modera\ConfigBundle\ModeraConfigBundle;
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
class ModeraConfigExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.php');

        /*
        $kernelBundles = $container->getParameter('kernel.bundles');
        if (isset($kernelBundles['ModeraSecurityBundle']) && null === $config['owner_entity']) {
            $config['owner_entity'] = 'Modera\SecurityBundle\Entity\User';
        }
        */

        if (\is_string($config['owner_entity'] ?? null)) {
            $listener = $container->getDefinition(OwnerRelationMappingListener::class);

            $listener->addTag('doctrine.event_listener', [
                'event' => 'loadClassMetadata',
            ]);
        }

        $container->setParameter(ModeraConfigBundle::CONFIG_KEY, $config);

        if (\class_exists(Application::class)) {
            try {
                $loader->load('console.php');
            } catch (\Exception $e) {
            }
        }
    }
}

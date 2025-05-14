<?php

namespace Modera\FileUploaderBundle\DependencyInjection;

use Modera\FileRepositoryBundle\Repository\FileRepository;
use Modera\FileUploaderBundle\Uploading\AllExposedRepositoriesGateway;
use Modera\FileUploaderBundle\Uploading\ExposedGatewayProvider;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link https://symfony.com/doc/current/bundles/extension.html}
 */
class ModeraFileUploaderExtension extends Extension
{
    public const CONFIG_KEY = 'modera_file_uploader.config';

    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('controller.php');
        $loader->load('services.php');

        $container->setParameter(self::CONFIG_KEY, $config);
        $container->setParameter('modera_file_uploader.is_enabled', $config['is_enabled']);
        $container->setParameter('modera_file_uploader.uploader_url', $config['url']);

        if (true === $config['expose_all_repositories']) {
            $gateway = new Definition(AllExposedRepositoriesGateway::class);
            $gateway->addArgument(new Reference(FileRepository::class));

            $container->setDefinition('modera_file_uploader.uploading.all_exposed_repositories_gateway', $gateway);

            $provider = new Definition(ExposedGatewayProvider::class);
            $provider->addArgument(new Reference('modera_file_uploader.uploading.all_exposed_repositories_gateway'));
            $provider->addTag('modera_file_uploader.uploading.gateways_provider');

            $container->setDefinition('modera_file_uploader.uploading.all_exposed_repositories_gateway_provider', $provider);
        }
    }
}

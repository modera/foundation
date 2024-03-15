<?php

namespace Modera\MjrIntegrationBundle\Contributions;

use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\MjrIntegrationBundle\AssetsHandling\AssetsProviderInterface;
use Modera\MjrIntegrationBundle\DependencyInjection\ModeraMjrIntegrationExtension;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides service definitions for client-side dependency injection container.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2013 Modera Foundation
 */
class ServiceDefinitionsProvider implements ContributorInterface
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getItems(): array
    {
        /** @var array{'client_runtime_config_provider_url': string} $bundleConfig */
        $bundleConfig = $this->container->getParameter(ModeraMjrIntegrationExtension::CONFIG_KEY);

        $services = [
            'config_provider' => [
                'className' => 'MF.runtime.config.AjaxConfigProvider',
                'args' => [
                    ['url' => $bundleConfig['client_runtime_config_provider_url']],
                ],
            ],
        ];

        /** @var AssetsProviderInterface $assetsProvider */
        $assetsProvider = $this->container->get('modera_mjr_integration.assets_handling.assets_provider');

        $jsAssets = $assetsProvider->getJavascriptAssets(AssetsProviderInterface::TYPE_NON_BLOCKING);
        $cssAssets = $assetsProvider->getCssAssets(AssetsProviderInterface::TYPE_NON_BLOCKING);

        if (\count(\array_merge($jsAssets, $cssAssets)) > 0) {
            $services = \array_merge($services, [
                'non_blocking_assets_loader' => [
                    'className' => 'MF.misc.NonBlockingAssetsLoader',
                    'args' => [
                        [
                            'js' => $jsAssets,
                            'css' => $cssAssets,
                        ],
                    ],
                ],
                'non_blocking_assets_workench_loading_blocking_plugin' => [
                    'className' => 'Modera.mjrintegration.runtime.plugin.WorkbenchLoadingBlockingPlugin',
                    'tags' => ['runtime_plugin'],
                ],
            ]);
        }

        return $services;
    }
}

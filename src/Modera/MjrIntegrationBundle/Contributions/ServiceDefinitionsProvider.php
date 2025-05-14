<?php

namespace Modera\MjrIntegrationBundle\Contributions;

use Modera\ExpanderBundle\Ext\AsContributorFor;
use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\MjrIntegrationBundle\AssetsHandling\AssetsProviderInterface;

/**
 * Provides service definitions for client-side dependency injection container.
 *
 * @copyright 2013 Modera Foundation
 */
#[AsContributorFor('modera_mjr_integration.csdi.service_definitions')]
class ServiceDefinitionsProvider implements ContributorInterface
{
    /**
     * @param array{'client_runtime_config_provider_url': string} $bundleConfig
     */
    public function __construct(
        private readonly AssetsProviderInterface $assetsProvider,
        private readonly array $bundleConfig,
    ) {
    }

    public function getItems(): array
    {
        $services = [
            'config_provider' => [
                'className' => 'MF.runtime.config.AjaxConfigProvider',
                'args' => [
                    ['url' => $this->bundleConfig['client_runtime_config_provider_url']],
                ],
            ],
        ];

        $jsAssets = $this->assetsProvider->getJavascriptAssets(AssetsProviderInterface::TYPE_NON_BLOCKING);
        $cssAssets = $this->assetsProvider->getCssAssets(AssetsProviderInterface::TYPE_NON_BLOCKING);

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

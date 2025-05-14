<?php

namespace Modera\MjrIntegrationBundle\Contributions;

use Modera\ExpanderBundle\Ext\AsContributorFor;
use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\MjrIntegrationBundle\Config\MainConfigInterface;

/**
 * @internal
 *
 * @copyright 2016 Modera Foundation
 */
#[AsContributorFor('modera_mjr_integration.csdi.service_definitions')]
class ClientDiServiceDefinitionsProvider implements ContributorInterface
{
    public function __construct(
        private readonly MainConfigInterface $config,
    ) {
    }

    public function getItems(): array
    {
        return [
            'page_title_monitoring_plugin' => [
                'className' => 'Modera.mjrintegration.runtime.titlehandling.MonitoringPlugin',
                'args' => [
                    [
                        'pageTitleMgr' => '@page_title_mgr',
                    ],
                ],
                'tags' => ['runtime_plugin'],
            ],
            'page_title_mgr' => [
                'className' => 'Modera.mjrintegration.runtime.titlehandling.PageTitleManager',
                'args' => [
                    [
                        'application' => '@application',
                        'titlePattern' => $this->config->getTitle(),
                    ],
                ],
            ],
        ];
    }
}

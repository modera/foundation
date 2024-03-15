<?php

namespace Modera\MjrIntegrationBundle\Contributions;

use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\MjrIntegrationBundle\Config\MainConfigInterface;

/**
 * @internal
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
class ClientDiServiceDefinitionsProvider implements ContributorInterface
{
    private MainConfigInterface $config;

    public function __construct(MainConfigInterface $config)
    {
        $this->config = $config;
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

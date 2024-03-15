<?php

namespace Modera\BackendDashboardBundle\Contributions;

use Modera\ExpanderBundle\Ext\ContributorInterface;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2017 Modera Foundation
 */
class ClassLoaderMappingsProvider implements ContributorInterface
{
    private ConfigMergersProvider $configMergersProvider;

    /**
     * @var array<string, string>
     */
    private ?array $items = null;

    public function __construct(ConfigMergersProvider $configMergersProvider)
    {
        $this->configMergersProvider = $configMergersProvider;
    }

    public function getItems(): array
    {
        if (!$this->items) {
            $this->items = [];

            // Register dashboard namespace, if landing section not dashboard
            $landingSection = $this->configMergersProvider->getUserLandingSection();
            if ('dashboard' !== $landingSection) {
                $this->items['Modera.backend.dashboard'] = '/bundles/moderabackenddashboard/js';
            }
        }

        return $this->items;
    }
}

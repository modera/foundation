<?php

namespace Modera\BackendDashboardBundle\Contributions;

use Sli\ExpanderBundle\Ext\ContributorInterface;
use Modera\BackendDashboardBundle\Contributions\ConfigMergersProvider;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2017 Modera Foundation
 */
class ClassLoaderMappingsProvider implements ContributorInterface
{
    /**
     * @var ConfigMergersProvider
     */
    private $configMergersProvider;

    /**
     * @var array
     */
    private $items = array();

    /**
     * @param ConfigMergersProvider $configMergersProvider
     */
    public function __construct(ConfigMergersProvider $configMergersProvider)
    {
        $this->configMergersProvider = $configMergersProvider;
    }

    /**
     * @inheritDoc
     */
    public function getItems()
    {
        if (!$this->items) {
            $landingSection = $this->configMergersProvider->getUserLandingSection();

            // Register dashboard namespace, if landing section not dashboard
            if ('dashboard' !== $landingSection) {
                $this->items = array(
                    'Modera.backend.dashboard' => '/bundles/moderabackenddashboard/js'
                );
            }
        }

        return $this->items;
    }
}
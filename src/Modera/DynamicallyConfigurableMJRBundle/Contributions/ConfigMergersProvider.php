<?php

namespace Modera\DynamicallyConfigurableMJRBundle\Contributions;

use Sli\ExpanderBundle\Ext\ContributorInterface;
use Modera\MjrIntegrationBundle\Config\ConfigMergerInterface;
use Modera\ConfigBundle\Config\ConfigurationEntriesManagerInterface;
use Modera\DynamicallyConfigurableMJRBundle\ModeraDynamicallyConfigurableMJRBundle as Bundle;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2020 Modera Foundation
 */
class ConfigMergersProvider implements ContributorInterface, ConfigMergerInterface
{
    /**
     * @var ConfigurationEntriesManagerInterface
     */
    private $mgr;

    /**
     * @param ConfigurationEntriesManagerInterface $mgr
     */
    public function __construct(ConfigurationEntriesManagerInterface $mgr)
    {
        $this->mgr = $mgr;
    }

    /**
     * @param array $currentConfig
     *
     * @return array
     */
    public function merge(array $currentConfig)
    {
        $logoUrl = $this->mgr->findOneByNameOrDie(Bundle::CONFIG_LOGO_URL)->getValue();

        return array_merge($currentConfig, array(
            'modera_dynamically_configurable_mjr' => array(
                'logo_url' => $logoUrl,
            ),
        ));
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getItems()
    {
        return array($this);
    }
}

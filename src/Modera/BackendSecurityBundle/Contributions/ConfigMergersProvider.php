<?php

namespace Modera\BackendSecurityBundle\Contributions;

use Sli\ExpanderBundle\Ext\ContributorInterface;
use Modera\MjrIntegrationBundle\Config\ConfigMergerInterface;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2017 Modera Foundation
 */
class ConfigMergersProvider implements ContributorInterface, ConfigMergerInterface
{
    /**
     * @var array
     */
    private $config = array();

    /**
     * @param array $config
     */
    public function __construct(array $config = array())
    {
        $this->config = $config;
    }

    /**
     * @param array $currentConfig
     *
     * @return array
     */
    public function merge(array $currentConfig)
    {
        return array_merge($currentConfig, array(
            'modera_backend_security' => array(
                'hideDeleteUserFunctionality' => $this->config['hide_delete_user_functionality'],
            ),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        return array($this);
    }
}

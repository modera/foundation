<?php

namespace Modera\BackendSecurityBundle\Contributions;

use Sli\ExpanderBundle\Ext\ContributorInterface;
use Modera\MjrIntegrationBundle\Config\ConfigMergerInterface;

/**
 * @internal
 *
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2017 Modera Foundation
 */
class ConfigMergersProvider implements ContributorInterface, ConfigMergerInterface
{
    /**
     * @var array
     */
    private $semanticConfig = array();

    /**
     * @param array $semanticConfig
     */
    public function __construct(array $semanticConfig = array())
    {
        $this->semanticConfig = $semanticConfig;
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
                'hideDeleteUserFunctionality' => $this->semanticConfig['hide_delete_user_functionality'],
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

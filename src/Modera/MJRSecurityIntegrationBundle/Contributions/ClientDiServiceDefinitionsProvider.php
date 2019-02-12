<?php

namespace Modera\MJRSecurityIntegrationBundle\Contributions;

use Sli\ExpanderBundle\Ext\ContributorInterface;

/**
 * Provides service definitions for client-side dependency injection container.
 *
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2019 Modera Foundation
 */
class ClientDiServiceDefinitionsProvider implements ContributorInterface
{
    /**
     * @var array
     */
    private $securityConfig = array();

    /**
     * @param array $securityConfig
     */
    public function __construct(array $securityConfig = array())
    {
        $this->securityConfig = $securityConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        if (isset($this->securityConfig['switch_user']) && $this->securityConfig['switch_user']) {
            $role = $this->securityConfig['switch_user']['role'];

            return array(
                'modera_mjr_security_integration.user_settings_contributor' => array(
                    'className' => 'Modera.mjrsecurityintegration.runtime.UserSettingsContributor',
                    'args' => ['@application', $role],
                    'tags' => ['shared_activities_provider'],
                ),
            );
        }

        return array();
    }
}

<?php

namespace Modera\MJRSecurityIntegrationBundle\Contributions;

use Modera\ExpanderBundle\Ext\AsContributorFor;
use Modera\ExpanderBundle\Ext\ContributorInterface;

/**
 * Provides service definitions for client-side dependency injection container.
 *
 * @copyright 2019 Modera Foundation
 */
#[AsContributorFor('modera_mjr_integration.csdi.service_definitions')]
class ClientDiServiceDefinitionsProvider implements ContributorInterface
{
    /**
     * @param array<string, mixed> $securityConfig
     */
    public function __construct(
        private readonly array $securityConfig = [],
    ) {
    }

    public function getItems(): array
    {
        if (isset($this->securityConfig['switch_user']) && $this->securityConfig['switch_user']) {
            /** @var array{'role': string} $switchUser */
            $switchUser = $this->securityConfig['switch_user'];

            return [
                'modera_mjr_security_integration.user_settings_contributor' => [
                    'className' => 'Modera.mjrsecurityintegration.runtime.UserSettingsContributor',
                    'args' => ['@application', $switchUser['role']],
                    'tags' => ['shared_activities_provider'],
                ],
            ];
        }

        return [];
    }
}

<?php

namespace Modera\BackendDashboardBundle\Contributions;

use Modera\ExpanderBundle\Ext\ContributorInterface;

/**
 * @author    Alex Rudakov <alexandr.rudakov@modera.org>
 * @copyright 2014 Modera Foundation
 */
class ClientDiServiceDefinitionsProvider implements ContributorInterface
{
    public function getItems(): array
    {
        return [
            'modera_backend_dashboard.user_dashboard_settings_window_contributor' => [
                'className' => 'Modera.backend.dashboard.runtime.UserDashboardSettingsWindowContributor',
                'args' => ['@application'],
                'tags' => ['shared_activities_provider'],
            ],
            'modera_backend_dashboard.settings_window_view_contributor' => [
                'className' => 'Modera.backend.dashboard.runtime.SettingsWindowContributor',
                'args' => ['@application'],
                'tags' => ['shared_activities_provider'],
            ],
        ];
    }
}

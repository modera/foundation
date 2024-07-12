<?php

namespace Modera\BackendSecurityBundle\Contributions;

use Modera\ExpanderBundle\Ext\ContributorInterface;

/**
 * @internal
 *
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class ClientDiServiceDefinitionsProvider implements ContributorInterface
{
    public function getItems(): array
    {
        return [
            'modera_backend_security.user.edit_window_contributor' => [
                'className' => 'Modera.backend.security.toolscontribution.runtime.user.EditWindowContributor',
                'args' => ['@application'],
                'tags' => ['shared_activities_provider'],
            ],
            'modera_backend_security.user.password_window_contributor' => [
                'className' => 'Modera.backend.security.toolscontribution.runtime.user.PasswordWindowContributor',
                'args' => ['@application'],
                'tags' => ['shared_activities_provider'],
            ],
        ];
    }
}

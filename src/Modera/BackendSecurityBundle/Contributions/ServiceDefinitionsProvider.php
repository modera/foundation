<?php

namespace Modera\BackendSecurityBundle\Contributions;

use Modera\ExpanderBundle\Ext\AsContributorFor;
use Modera\ExpanderBundle\Ext\ContributorInterface;

/**
 * @internal
 *
 * @copyright 2017 Modera Foundation
 */
#[AsContributorFor('modera_mjr_integration.csdi.service_definitions')]
class ServiceDefinitionsProvider implements ContributorInterface
{
    public function getItems(): array
    {
        return [
            'modera_backend_security.password_rotation_plugin' => [
                'className' => 'Modera.backend.security.passwordrotation.PasswordRotationPlugin',
                'args' => ['@workbench'],
                'tags' => ['runtime_plugin'],
            ],
        ];
    }
}

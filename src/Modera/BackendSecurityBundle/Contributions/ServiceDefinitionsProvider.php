<?php

namespace Modera\BackendSecurityBundle\Contributions;

use Modera\ExpanderBundle\Ext\ContributorInterface;

/**
 * @internal
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2017 Modera Foundation
 */
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

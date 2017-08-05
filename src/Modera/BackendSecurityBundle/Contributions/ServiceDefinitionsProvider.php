<?php

namespace Modera\BackendSecurityBundle\Contributions;

use Sli\ExpanderBundle\Ext\ContributorInterface;

/**
 * @internal
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2017 Modera Foundation
 */
class ServiceDefinitionsProvider implements ContributorInterface
{
    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        return array(
            'modera_backend_security.password_rotation_plugin' => array(
                'className' => 'Modera.backend.security.passwordrotation.PasswordRotationPlugin',
                'args' => ['@workbench'],
                'tags' => ['runtime_plugin'],
            ),
        );
    }
}
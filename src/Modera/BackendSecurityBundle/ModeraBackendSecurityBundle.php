<?php

namespace Modera\BackendSecurityBundle;

use Modera\ExpanderBundle\Ext\ExtensionPoint;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class ModeraBackendSecurityBundle extends Bundle
{
    public const ROLE_ACCESS_BACKEND_TOOLS_SECURITY_SECTION = 'ROLE_ACCESS_BACKEND_TOOLS_SECURITY_SECTION';
    public const ROLE_MANAGE_USER_PROFILE_INFORMATION = 'ROLE_MANAGE_USER_PROFILE_INFORMATION';
    public const ROLE_MANAGE_USER_ACCOUNTS = 'ROLE_MANAGE_USER_ACCOUNTS';
    public const ROLE_MANAGE_USER_PROFILES = 'ROLE_MANAGE_USER_PROFILES';
    public const ROLE_MANAGE_PERMISSIONS = 'ROLE_MANAGE_PERMISSIONS';

    public function build(ContainerBuilder $container): void
    {
        $securitySectionsExtensionPoint = new ExtensionPoint('modera_backend_security.sections');
        $securitySectionsExtensionPoint->setDescription('Allows to contribute new sections to Security settings');
        $container->addCompilerPass($securitySectionsExtensionPoint->createCompilerPass());
    }
}

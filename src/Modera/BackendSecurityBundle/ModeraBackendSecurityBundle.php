<?php

namespace Modera\BackendSecurityBundle;

use Sli\ExpanderBundle\Ext\ExtensionPoint;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Modera\BackendSecurityBundle\DependencyInjection\ServiceAliasCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class ModeraBackendSecurityBundle extends Bundle
{
    const ROLE_ACCESS_BACKEND_TOOLS_SECURITY_SECTION = 'ROLE_ACCESS_BACKEND_TOOLS_SECURITY_SECTION';
    const ROLE_MANAGE_USER_PROFILES = 'ROLE_MANAGE_USER_PROFILES';
    const ROLE_MANAGE_PERMISSIONS = 'ROLE_MANAGE_PERMISSIONS';
    const ROLE_MANAGE_USER_PROFILE_INFORMATION = 'ROLE_MANAGE_USER_PROFILE_INFORMATION';

    public function build(ContainerBuilder $container)
    {
        $securitySectionsExtensionPoint = new ExtensionPoint('modera_backend_security.sections');
        $securitySectionsExtensionPoint->setDescription('Allows to contribute new sections to Security settings');
        $container->addCompilerPass($securitySectionsExtensionPoint->createCompilerPass());

        $container->addCompilerPass(new ServiceAliasCompilerPass());
    }
}

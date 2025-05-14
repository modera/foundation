<?php

namespace Modera\MJRSecurityIntegrationBundle;

use Modera\ExpanderBundle\Ext\ExtensionPoint;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @copyright 2013 Modera Foundation
 */
class ModeraMJRSecurityIntegrationBundle extends Bundle
{
    public const ROLE_BACKEND_USER = 'ROLE_BACKEND_USER';

    public function build(ContainerBuilder $container): void
    {
        $clientDiServiceDefinitionsProvider = new ExtensionPoint('modera_mjr_security_integration.client_di_service_defs');
        $clientDiServiceDefinitionsProvider->setDescription(
            'Allows to contribute client-side DI container service definitions that will be configured only after user has successfully authenticated.'
        );
        $container->addCompilerPass($clientDiServiceDefinitionsProvider->createCompilerPass());
    }
}

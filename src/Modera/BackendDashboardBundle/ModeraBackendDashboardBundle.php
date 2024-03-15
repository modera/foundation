<?php

namespace Modera\BackendDashboardBundle;

use Modera\ExpanderBundle\Ext\ExtensionPoint;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @deprecated will be removed in next version
 *
 * Class ModeraBackendDashboardBundle
 *
 * @author    Alex Rudakov <alexandr.rudakov@modera.org>
 * @copyright 2014 Modera Foundation
 */
class ModeraBackendDashboardBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $dashboardProvider = new ExtensionPoint('modera_backend_dashboard.dashboard');
        $dashboardProvider->setDescription('Allows to contribute new dashboard panels to Backend/Dashboard section.');

        $container->addCompilerPass($dashboardProvider->createCompilerPass());
    }
}

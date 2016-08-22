<?php

namespace Modera\BackendDashboardBundle\Tests\DependencyInjection;

use Modera\BackendDashboardBundle\Contributions\DashboardProvider;
use Modera\BackendDashboardBundle\DependencyInjection\ModeraBackendDashboardExtension;
use Modera\BackendDashboardBundle\ModeraBackendDashboardBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @copyright 2013 Modera Foundation
 * @author Alex Rudakov <alexandr.rudakov@modera.net>
 */
class ModeraBackendDashboardExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testConfigLoad()
    {
        $builder = new ContainerBuilder();

        $bundle = new ModeraBackendDashboardBundle();

        $bundle->build($builder);

        $ext = new ModeraBackendDashboardExtension();
        $ext->load(array(), $builder);

        $builder->compile();

        $this->assertTrue($builder->has('modera_backend_dashboard.contributions.menu_items_provider'));
        $menuProvider = $builder->getDefinition('modera_backend_dashboard.contributions.menu_items_provider');
        $this->assertEquals('Modera\BackendDashboardBundle\Contributions\MenuItemsProvider', $menuProvider->getClass());
        $this->assertTrue($menuProvider->hasTag('modera_mjr_integration.menu.menu_items_provider'));

        // service provided by Expander bundle
        $this->assertTrue($builder->has('modera_backend_dashboard.dashboard_provider'));

        $this->assertTrue($builder->has('modera_backend_dashboard.dashboard_service'));
        $arg = $builder->getDefinition('modera_backend_dashboard.dashboard_service')->getArgument(0);
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $arg);
        /** @var \Symfony\Component\DependencyInjection\Reference $arg */
        $this->assertEquals('modera_backend_dashboard.dashboard_provider', $arg->__toString());

        $this->assertTrue($builder->has('modera_backend_dashboard.contributions.config_mergers_provider'));
        $configProvider = $builder->getDefinition('modera_backend_dashboard.contributions.config_mergers_provider');
        $this->assertTrue($configProvider->hasTag('modera_mjr_integration.config.config_mergers_provider'));
    }
} 
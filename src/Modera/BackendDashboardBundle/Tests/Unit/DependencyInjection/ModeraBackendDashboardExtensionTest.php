<?php

namespace Modera\BackendDashboardBundle\Tests\Unit\DependencyInjection;

use Modera\BackendDashboardBundle\DependencyInjection\ModeraBackendDashboardExtension;
use Modera\BackendDashboardBundle\ModeraBackendDashboardBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @copyright 2013 Modera Foundation
 * @author Alex Rudakov <alexandr.rudakov@modera.net>
 */
class ModeraBackendDashboardExtensionTest extends \PHPUnit\Framework\TestCase
{
    public function testConfigLoad()
    {
        $builder = new ContainerBuilder();

        // external services
        $builder->set('doctrine.orm.entity_manager', new \stdClass());
        $builder->set('security.token_storage', new \stdClass());

        $bundle = new ModeraBackendDashboardBundle();

        $bundle->build($builder);

        $definition = new Definition(\stdClass::class);
        $builder->setDefinition('modera_backend_translations_tool.handling.extjs_translation_handler', $definition);
        $builder->setDefinition('modera_translations.handling.php_classes_translation_handler', $definition);

        $ext = new ModeraBackendDashboardExtension();
        $ext->load(array(), $builder);

        $builder->compile();

        $this->assertTrue($builder->has('modera_backend_dashboard.contributions.menu_items_provider'));
        $menuProvider = $builder->getDefinition('modera_backend_dashboard.contributions.menu_items_provider');
        $this->assertEquals('Modera\BackendDashboardBundle\Contributions\MenuItemsProvider', $menuProvider->getClass());
        $this->assertTrue($menuProvider->hasTag('modera_mjr_integration.menu.menu_items_provider'));

        // service provided by Expander bundle
        $this->assertTrue($builder->has('modera_backend_dashboard.dashboard_provider'));

        $this->assertTrue($builder->has('modera_backend_dashboard.contributions.config_mergers_provider'));
        $configProvider = $builder->getDefinition('modera_backend_dashboard.contributions.config_mergers_provider');
        $this->assertTrue($configProvider->hasTag('modera_mjr_integration.config.config_mergers_provider'));
    }
}

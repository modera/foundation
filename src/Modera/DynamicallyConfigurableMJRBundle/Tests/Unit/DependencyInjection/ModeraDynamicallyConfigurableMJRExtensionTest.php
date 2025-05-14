<?php

namespace Modera\DynamicallyConfigurableMJRBundle\Tests\Unit\DependencyInjection;

use Modera\DynamicallyConfigurableMJRBundle\Contributions\ClassLoaderMappingsProvider;
use Modera\DynamicallyConfigurableMJRBundle\Contributions\ConfigEntriesProvider;
use Modera\DynamicallyConfigurableMJRBundle\Contributions\SettingsSectionsProvider;
use Modera\DynamicallyConfigurableMJRBundle\DependencyInjection\ModeraDynamicallyConfigurableMJRExtension;
use Modera\ExpanderBundle\Ext\AsContributorFor;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ModeraDynamicallyConfigurableMJRExtensionTest extends \PHPUnit\Framework\TestCase
{
    public function testLoad(): void
    {
        $ext = new ModeraDynamicallyConfigurableMJRExtension();

        $builder = new ContainerBuilder();

        $ext->load([], $builder);

        $this->assertAsContributorFor($builder, ClassLoaderMappingsProvider::class, 'modera_mjr_integration.class_loader_mappings');
        $this->assertAsContributorFor($builder, ConfigEntriesProvider::class, 'modera_config.config_entries');
        $this->assertAsContributorFor($builder, SettingsSectionsProvider::class, 'modera_backend_tools_settings.contributions.sections');
    }

    private function assertAsContributorFor(ContainerBuilder $builder, string $serviceId, string $extensionPointId): void
    {
        $def = $builder->getDefinition($serviceId);
        $class = $def->getClass() ?? $serviceId;
        $reflectionClass = $builder->getReflectionClass($class);
        $attribute = $reflectionClass->getAttributes(AsContributorFor::class)[0] ?? null;
        /** @var ?AsContributorFor $asContributorFor */
        $asContributorFor = $attribute?->newInstance();
        $this->assertEquals($extensionPointId, $asContributorFor?->id);
    }
}

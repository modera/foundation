<?php

namespace Modera\BackendConfigUtilsBundle\Tests\Unit\DependencyInjection;

use Modera\BackendConfigUtilsBundle\Contributions\ClassLoaderMappingsProvider;
use Modera\BackendConfigUtilsBundle\DependencyInjection\ModeraBackendConfigUtilsExtension;
use Modera\ExpanderBundle\Ext\AsContributorFor;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ModeraBackendConfigUtilsExtensionTest extends \PHPUnit\Framework\TestCase
{
    public function testLoad(): void
    {
        $ext = new ModeraBackendConfigUtilsExtension();

        $builder = new ContainerBuilder();

        $ext->load([], $builder);

        $classLoaderMappingProvider = $builder->getDefinition(ClassLoaderMappingsProvider::class);
        $class = $classLoaderMappingProvider->getClass() ?? ClassLoaderMappingsProvider::class;
        $reflectionClass = $builder->getReflectionClass($class);
        $attribute = $reflectionClass->getAttributes(AsContributorFor::class)[0] ?? null;
        /** @var ?AsContributorFor $asContributorFor */
        $asContributorFor = $attribute?->newInstance();
        $this->assertEquals('modera_mjr_integration.class_loader_mappings', $asContributorFor?->id);
    }
}

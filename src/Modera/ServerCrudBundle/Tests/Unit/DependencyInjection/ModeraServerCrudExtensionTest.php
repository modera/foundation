<?php

namespace Modera\ServerCrudBundle\Tests\Unit\DependencyInjection;

use Modera\ExpanderBundle\Ext\AsContributorFor;
use Modera\ServerCrudBundle\Contributions\ControllerActionInterceptorsProvider;
use Modera\ServerCrudBundle\DependencyInjection\ModeraServerCrudExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ModeraServerCrudExtensionTest extends \PHPUnit\Framework\TestCase
{
    public function testIfItHasServicesTagged(): void
    {
        $ext = new ModeraServerCrudExtension();
        $container = new ContainerBuilder();

        $ext->load([], $container);

        $classLoaderMappingProvider = $container->getDefinition(ControllerActionInterceptorsProvider::class);
        $class = $classLoaderMappingProvider->getClass() ?? ControllerActionInterceptorsProvider::class;
        $reflectionClass = $container->getReflectionClass($class);
        $attribute = $reflectionClass->getAttributes(AsContributorFor::class)[0] ?? null;
        /** @var ?AsContributorFor $asContributorFor */
        $asContributorFor = $attribute?->newInstance();
        $this->assertEquals('modera_server_crud.intercepting.cai', $asContributorFor?->id);
    }
}

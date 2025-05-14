<?php

namespace Modera\ExpanderBundle\Tests\Unit\Contributing;

use Modera\ExpanderBundle\Contributing\BundleContributorAdapter;
use Modera\ExpanderBundle\Contributing\ExtensionPointsAwareBundlesCollectorCompilerPass;
use Modera\ExpanderBundle\Tests\Unit\FooDummyBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DummyContainerBuilder extends ContainerBuilder
{
    public array $bundles = [];

    /**
     * @var Definition[]
     */
    public array $definitions = [];

    public function setDefinition(string $id, Definition $definition): Definition
    {
        return $this->definitions[$id] = $definition;
    }

    public function getParameter(string $name)
    {
        if ('kernel.bundles' === $name) {
            return $this->bundles;
        }

        return null;
    }
}

class FooDummyBundle1 extends FooDummyBundle
{
    public function __construct()
    {
        $this->map = [
            'foo_ep' => [],
            'bar_ep' => [],
        ];
    }
}

class FooDummyBundle2 extends Bundle
{
}

class FooDummyBundle3 extends FooDummyBundle
{
    public function __construct()
    {
        $this->map = [
            'baz_ep' => [],
        ];
    }
}

class ExtensionPointsAwareBundlesCollectorCompilerPassTest extends \PHPUnit\Framework\TestCase
{
    public function testProcess(): void
    {
        $bundle1 = new FooDummyBundle1();
        $bundle3 = new FooDummyBundle3();

        $containerBuilder = new DummyContainerBuilder();

        $containerBuilder->bundles = [
            'FooDummyBundle1' => FooDummyBundle1::class,
            'FooDummyBundle2' => FooDummyBundle2::class,
            'FooDummyBundle3' => FooDummyBundle3::class,
        ];

        $cp = new ExtensionPointsAwareBundlesCollectorCompilerPass();
        $cp->process($containerBuilder);

        $definitions = [];
        foreach ($containerBuilder->definitions as $key => $definition) {
            if (BundleContributorAdapter::class === $definition->getClass()) {
                $definitions[$key] = $definition;
            }
        }

        $this->assertEquals(3, \count($definitions));
        foreach ($definitions as $definition) {
            $this->assertInstanceOf(Definition::class, $definition);

            $args = $definition->getArguments();
            $this->assertEquals(3, \count($args));

            $this->assertInstanceOf(Reference::class, $args[0]);
            /** @var Reference $kernelArg */
            $kernelArg = $args[0];
            $this->assertEquals('kernel', (string) $kernelArg);

            $this->assertNotNull($args[1]);
            $this->assertNotNull($args[2]);
        }

        /** @var Definition[] $definitions */
        $definitions = \array_values($definitions);

        $definition1 = $definitions[0];
        $this->assertEquals($bundle1->getName(), $definition1->getArgument(1));
        $this->assertEquals('foo_ep', $definition1->getArgument(2));

        $definition2 = $definitions[1];
        $this->assertEquals($bundle1->getName(), $definition2->getArgument(1));
        $this->assertEquals('bar_ep', $definition2->getArgument(2));

        $definition3 = $definitions[2];
        $this->assertEquals($bundle3->getName(), $definition3->getArgument(1));
        $this->assertEquals('baz_ep', $definition3->getArgument(2));
    }
}

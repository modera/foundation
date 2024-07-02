<?php

namespace Modera\ExpanderBundle\Tests\Unit\Contributing;

use Modera\ExpanderBundle\Contributing\BundleContributorAdapter;
use Modera\ExpanderBundle\Contributing\ExtensionPointsAwareBundlesCollectorCompilerPass;
use Modera\ExpanderBundle\Tests\Unit\FooDummyBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class DummyContainerBuilder extends ContainerBuilder
{
    /**
     * @var Definition[]
     */
    public array $definitions = [];

    public function setDefinition($id, Definition $definition): void
    {
        $this->definitions[$id] = $definition;
    }
}

class ExtensionPointsAwareBundlesCollectorCompilerPassTest extends \PHPUnit\Framework\TestCase
{
    public function testProcess()
    {
        $bundle1 = new FooDummyBundle();
        $bundle1->map = [
            'foo_ep' => [],
            'bar_ep' => [],
        ];

        $bundle2 = \Phake::mock('Symfony\Component\HttpKernel\Bundle\BundleInterface');
        $bundle3 = new FooDummyBundle();
        $bundle3->map = [
            'baz_ep' => [],
        ];

        $kernel = \Phake::mock('Symfony\Component\HttpKernel\KernelInterface');
        \Phake::when($kernel)->getBundles()->thenReturn([$bundle1, $bundle2,  $bundle3]);

        $containerBuilder = new DummyContainerBuilder();

        $cp = new ExtensionPointsAwareBundlesCollectorCompilerPass($kernel);
        $cp->process($containerBuilder);

        $definitions = [];
        foreach ($containerBuilder->definitions as $key => $definition) {
            if ($definition->getClass() === BundleContributorAdapter::class) {
                $definitions[$key] = $definition;
            }
        }

        $this->assertEquals(3, \count($definitions));
        foreach ($definitions as $definition) {
            $this->assertInstanceOf('Symfony\Component\DependencyInjection\Definition', $definition);

            $args = $definition->getArguments();
            $this->assertEquals(3, \count($args));

            $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $args[0]);
            /** @var Reference $kernelArg*/
            $kernelArg = $args[0];
            $this->assertEquals('kernel', (string) $kernelArg);

            $this->assertNotNull($args[1]);
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
        $this->assertEquals($bundle1->getName(), $definition3->getArgument(1));
        $this->assertEquals('baz_ep', $definition3->getArgument(2));
    }
}

<?php

namespace Modera\RoutingBundle\Tests\Unit;

use Modera\RoutingBundle\ModeraRoutingBundle;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ModeraRoutingBundleTest extends \PHPUnit\Framework\TestCase
{
    public function testBuild(): void
    {
        $containerBuilder = \Phake::mock(ContainerBuilder::class);
        \Phake::when($containerBuilder)
            ->addCompilerPass(\Phake::anyParameters())
            ->thenReturn($containerBuilder)
        ;

        $bundle = new ModeraRoutingBundle();

        $bundle->build($containerBuilder);

        \Phake::verify($containerBuilder, \Phake::atLeast(2))
            ->addCompilerPass(
                $this->isInstanceOf(CompilerPassInterface::class),
            )
        ;
    }
}

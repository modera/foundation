<?php

namespace Modera\RoutingBundle\Tests\Unit\DependencyInjection;

use Modera\RoutingBundle\DependencyInjection\DelegatingLoaderCloningCompilerPass;

class DelegatingLoaderCloningCompilerPassTest extends \PHPUnit\Framework\TestCase
{
    public function testProcess(): void
    {
        $routingLoaderWannaBe = \Phake::mock('Symfony\Component\DependencyInjection\Definition');
        $containerBuilder = \Phake::mock('Symfony\Component\DependencyInjection\ContainerBuilder');

        \Phake::when($containerBuilder)->getDefinition('routing.loader')->thenReturn($routingLoaderWannaBe);

        $cp = new DelegatingLoaderCloningCompilerPass();
        $cp->process($containerBuilder);

        \Phake::verify($containerBuilder)->setDefinition('modera_routing.symfony_delegating_loader', \Phake::capture($clonedDefinition));

        $this->assertInstanceOf(\get_class($routingLoaderWannaBe), $clonedDefinition);
    }
}

<?php

namespace Modera\ExpanderBundle\Tests\Unit\DependencyInjection;

use Modera\ExpanderBundle\DependencyInjection\CompositeContributorsProviderCompilerPass;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class CompositeContributorsProviderCompilerPassTest extends \PHPUnit\Framework\TestCase
{
    public function test__Construct()
    {
        $cp = new CompositeContributorsProviderCompilerPass('foo');
        $this->assertSame('foo', $cp->getProviderServiceId());
        $this->assertSame('foo', $cp->getContributorServiceTagName());
    }

    public function testProcess()
    {
        $cb = new MockContainerBuilder();
        $cb->services = [
            'service1foo' => 'def',
            'service2bar' => 'def',
        ];

        $cp = new CompositeContributorsProviderCompilerPass('fooServiceId', 'barServiceId');
        $cp->process($cb);

        $this->assertEquals(1, \count($cb->definitions));
        $this->assertArrayHasKey('fooServiceId', $cb->definitions);

        /** @var Definition $provider */
        $provider = $cb->definitions['fooServiceId'];
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Definition', $provider);
        $calls = $provider->getMethodCalls();
        $this->assertEquals(2, \count($calls));

        $this->assertContributor($calls[0], 'service1foo');
        $this->assertContributor($calls[1], 'service2bar');
    }

    private function assertContributor(array $methodCall, $refServiceId)
    {
        $this->assertEquals(2, \count($methodCall));
        $this->assertEquals('addContributor', $methodCall[0]);
        /** @var Reference $ref */
        $ref = $methodCall[1][0];
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $ref);
        $this->assertEquals($refServiceId, $ref->__toString());
    }
}

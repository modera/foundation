<?php

namespace Modera\ServerCrudBundle\Tests\Unit\DataMapping;

use Modera\ServerCrudBundle\DataMapping\MethodInvocation\MethodInvocationParametersProvider as Provider;
use Modera\ServerCrudBundle\DataMapping\MethodInvocation\Params;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FooEntity
{
    #[Params(['foo', 'bar'])]
    public function foo($fooService, $barService): void
    {
    }

    #[Params(['baz-service*'])]
    public function baz($bazService): void
    {
    }
}

class MockContainer extends Container
{
    public function get(string $id, int $invalidBehavior = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE): ?object
    {
        if ('baz-service' === $id && ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE === $invalidBehavior) {
            throw new \RuntimeException('When a service-name is marked with "*", then the invalidBehaviour must be NULL_ON_INVALID_REFERENCE');
        }
        $obj = new \stdClass();
        $obj->id = \sprintf('%s-service-instance', $id);

        return $obj;
    }
}

class MethodInvocationParametersProviderTest extends \PHPUnit\Framework\TestCase
{
    public function testGetParameters(): void
    {
        $c = new MockContainer();
        $p = new Provider($c);

        $params = $p->getParameters(__NAMESPACE__.'\\FooEntity', 'foo');
        $this->assertTrue(\is_array($params));
        $this->assertEquals(2, \count($params));
        $this->assertSame('foo-service-instance', $params[0]->id);
        $this->assertSame('bar-service-instance', $params[1]->id);

        $params = $p->getParameters(__NAMESPACE__.'\\FooEntity', 'baz');
    }
}

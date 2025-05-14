<?php

namespace Modera\ServerCrudBundle\Tests\Unit\QueryBuilder\ResolvingAssociatedModelSortingField;

use Modera\ServerCrudBundle\QueryBuilder\ResolvingAssociatedModelSortingField\ChainSortingFieldResolver;
use Modera\ServerCrudBundle\QueryBuilder\ResolvingAssociatedModelSortingField\SortingFieldResolverInterface;

class ChainSortingFieldResolverTest extends \PHPUnit\Framework\TestCase
{
    public function testAddAndAllMethods(): void
    {
        $c = new ChainSortingFieldResolver();

        $this->assertEquals(0, \count($c->all()));

        $resolver = $this->createMock(SortingFieldResolverInterface::class);

        $c->add($resolver);

        $resolvers = $c->all();

        $this->assertTrue(\is_array($resolvers));
        $this->assertArrayHasKey(0, $resolvers);
        $this->assertSame($resolver, $resolvers[0]);
    }

    private function createResolver($entityFqcn, $paramName, $resultValue): SortingFieldResolverInterface
    {
        $resolver = $this->createMock(SortingFieldResolverInterface::class);
        $resolver->expects($this->once())
            ->method('resolve')
            ->with($this->equalTo($entityFqcn), $this->equalTo($paramName))
            ->will($this->returnValue($resultValue))
        ;

        return $resolver;
    }

    public function testAddAndThenResolve(): void
    {
        $entityFqcn = 'foo';
        $paramName = 'barProperty';

        $resolver1 = $this->createResolver($entityFqcn, $paramName, null);
        $resolver2 = $this->createResolver($entityFqcn, $paramName, null);
        $resolver3 = $this->createResolver($entityFqcn, $paramName, 'fooResult');
        $resolver4 = $this->createMock(SortingFieldResolverInterface::class);
        $resolver4->expects($this->never())->method('resolve');

        $c = new ChainSortingFieldResolver();
        $c->add($resolver1);
        $c->add($resolver2);
        $c->add($resolver3);
        $c->add($resolver4);

        $this->assertEquals('fooResult', $c->resolve($entityFqcn, $paramName));
    }
}

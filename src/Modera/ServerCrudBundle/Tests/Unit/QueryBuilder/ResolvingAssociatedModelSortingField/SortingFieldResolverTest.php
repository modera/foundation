<?php

namespace Modera\ServerCrudBundle\Tests\Unit\QueryBuilder\ResolvingAssociatedModelSortingField;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use Modera\ServerCrudBundle\QueryBuilder\ResolvingAssociatedModelSortingField\QueryOrder;
use Modera\ServerCrudBundle\QueryBuilder\ResolvingAssociatedModelSortingField\SortingFieldResolver;

class FooEntity
{
    private string $bar;
}

#[QueryOrder('name')]
class BarEntity
{
    private string $name;

    #[QueryOrder('someField')]
    private string $baz;
}

class BazEntity
{
    private string $someField;

    private string $faa;
}

class FaaEntity
{
    private ?int $id = null;
}

class SortingFieldResolverTest extends \PHPUnit\Framework\TestCase
{
    private function createDoctrineRegistry($sourceEntity, $assocProperty, $targetEntity): ManagerRegistry
    {
        $fooMetadata = $this->createMock(ClassMetadata::class);
        $fooMetadata->expects($this->any())
            ->method('getAssociationMapping')
            ->with($assocProperty)
            ->will($this->returnValue(['targetEntity' => $targetEntity]))
        ;

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->any())
            ->method('getClassMetadata')
            ->with($sourceEntity)
            ->will($this->returnValue($fooMetadata))
        ;

        $doctrineRegistry = $this->createMock(ManagerRegistry::class);

        $doctrineRegistry->expects($this->any())
            ->method('getManagerForClass')
            ->will($this->returnValue($em))
        ;

        return $doctrineRegistry;
    }

    public function testResolveDefinedOnRelatedEntity(): void
    {
        $source = __NAMESPACE__.'\FooEntity';

        $r = new SortingFieldResolver($this->createDoctrineRegistry($source, 'bar', __NAMESPACE__.'\BarEntity'));
        $this->assertEquals('name', $r->resolve($source, 'bar'));
    }

    public function testResolveDefinedOnProperty(): void
    {
        $source = __NAMESPACE__.'\BarEntity';

        $r = new SortingFieldResolver($this->createDoctrineRegistry($source, 'baz', __NAMESPACE__.'\BazEntity'));
        $this->assertEquals('someField', $r->resolve($source, 'baz'));
    }

    public function testResolveWithDefaultProperty()
    {
        $source = __NAMESPACE__.'\BazEntity';

        $r = new SortingFieldResolver($this->createDoctrineRegistry($source, 'faa', __NAMESPACE__.'\FaaEntity'));
        $this->assertEquals('id', $r->resolve($source, 'faa'));
    }

    public function testResolveWithNonExistingDefaultProperty(): void
    {
        $this->expectException(\RuntimeException::class);

        $source = __NAMESPACE__.'\BazEntity';

        $r = new SortingFieldResolver($this->createDoctrineRegistry($source, 'faa', __NAMESPACE__.'\FaaEntity'), 'blah');
        $r->resolve($source, 'faa');
    }
}

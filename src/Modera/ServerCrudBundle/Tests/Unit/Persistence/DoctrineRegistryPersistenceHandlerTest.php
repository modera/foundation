<?php

namespace Modera\ServerCrudBundle\Tests\Unit\Persistence;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use Modera\ServerCrudBundle\Persistence\DoctrineRegistryPersistenceHandler;
use Modera\ServerCrudBundle\QueryBuilder\ArrayQueryBuilder;
use Modera\ServerCrudBundle\Tests\Functional\DummyUser;

class DummyAddress
{
    public ?string $id = null;

    public function __construct(?string $id = null)
    {
        $this->id = $id;
    }

    public function getId(): ?string
    {
        return $this->id;
    }
}

class DoctrineRegistryPersistenceHandlerTest extends \PHPUnit\Framework\TestCase
{
    public function testUpdateBatch(): void
    {
        $user1 = new DummyUser();
        $user1->id = 1;

        $address1 = new DummyAddress('foo-address');
        $address2 = new DummyAddress('bar-address');

        $em1 = $this->createDummyEntityManager(); // responsible for user1
        $em2 = $this->createDummyEntityManager(); // responsible for address1, address2

        $registry = $this->createDummyRegistry([
            \get_class($user1) => $em1,
            \get_class($address1) => $em2,
        ]);

        $handler = new DoctrineRegistryPersistenceHandler($registry, \Phake::mock(ArrayQueryBuilder::class));

        $handler->updateBatch([$user1, $address1, $address2]);

        // calls to flush() are expected to be aggregated
        \Phake::verify($em1, \Phake::times(1))
            ->flush()
        ;
        \Phake::verify($em2, \Phake::times(1))
            ->flush()
        ;

        \Phake::verify($em1, \Phake::times(1))
            ->persist($user1)
        ;

        \Phake::verify($em2, \Phake::times(1))
            ->persist($address1)
        ;
        \Phake::verify($em2, \Phake::times(1))
            ->persist($address2)
        ;
    }

    public function testRemove(): void
    {
        $user1 = new DummyUser();
        $user1->id = 1;

        $address1 = new DummyAddress('foo-address');
        $address2 = new DummyAddress('bar-address');

        $em1 = $this->createDummyEntityManager(); // responsible for user1
        $em2 = $this->createDummyEntityManager(); // responsible for address1, address2

        $registry = $this->createDummyRegistry([
            \get_class($user1) => $em1,
            \get_class($address1) => $em2,
        ]);

        $handler = new DoctrineRegistryPersistenceHandler($registry, \Phake::mock(ArrayQueryBuilder::class));

        $handler->remove([$user1, $address1, $address2]);

        // calls to flush() are expected to be aggregated
        \Phake::verify($em1, \Phake::times(1))
            ->flush()
        ;
        \Phake::verify($em2, \Phake::times(1))
            ->flush()
        ;

        \Phake::verify($em1, \Phake::times(1))
            ->remove($user1)
        ;

        \Phake::verify($em2, \Phake::times(1))
            ->remove($address1)
        ;
        \Phake::verify($em2, \Phake::times(1))
            ->remove($address2)
        ;
    }

    private function createDummyRegistry(array $classToEntityManagersMapping): ManagerRegistry
    {
        $r = \Phake::mock(ManagerRegistry::class);

        $meta = \Phake::mock(ClassMetadata::class);
        \Phake::when($meta)
            ->getSingleIdentifierFieldName()
            ->thenReturn('id')
        ;

        foreach ($classToEntityManagersMapping as $entityClass => $em) {
            \Phake::when($r)
                ->getManagerForClass($entityClass)
                ->thenReturn($em)
            ;

            \Phake::when($em)
                ->getClassMetadata($entityClass)
                ->thenReturn($meta)
            ;
        }

        return $r;
    }

    private function createDummyEntityManager(): EntityManagerInterface
    {
        return \Phake::mock(EntityManagerInterface::class);
    }
}

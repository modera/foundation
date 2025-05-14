<?php

namespace Modera\ServerCrudBundle\Tests\Unit\Persistence;

use Modera\ServerCrudBundle\Persistence\DelegatePersistenceHandler;
use Modera\ServerCrudBundle\Persistence\PersistenceHandlerInterface;

class DelegatePersistenceHandlerTest extends \PHPUnit\Framework\TestCase
{
    private DelegatePersistenceHandler $handler;

    private PersistenceHandlerInterface $delegate;

    protected function setUp(): void
    {
        $this->delegate = \Phake::mock(PersistenceHandlerInterface::class);
        $this->handler = new DelegatePersistenceHandler($this->delegate);
    }

    public function testResolveEntityPrimaryKeyFields(): void
    {
        \Phake::when($this->delegate)
            ->resolveEntityPrimaryKeyFields('foo')
            ->thenReturn(['bar-field'])
        ;

        $result = $this->handler->resolveEntityPrimaryKeyFields('foo');

        $this->assertEquals(['bar-field'], $result);
    }

    public function testSave(): void
    {
        $entity = new \stdClass();

        $this->handler->save($entity);

        \Phake::verify($this->delegate)
            ->save($entity)
        ;
    }

    public function testUpdate(): void
    {
        $entity = new \stdClass();

        $this->handler->update($entity);

        \Phake::verify($this->delegate)
            ->update($entity)
        ;
    }

    public function testUpdateBatch(): void
    {
        $entities = [new \stdClass(), new \stdClass()];

        $this->handler->updateBatch($entities);

        \Phake::verify($this->delegate)
            ->updateBatch($entities)
        ;
    }

    public function testQuery(): void
    {
        \Phake::when($this->delegate)
            ->query('foo', ['bar'])
            ->thenReturn(['mega-result'])
        ;

        $result = $this->handler->query('foo', ['bar']);

        \Phake::verify($this->delegate)
            ->query('foo', ['bar'])
        ;

        $this->assertEquals(['mega-result'], $result);
    }

    public function testRemove(): void
    {
        $entities = [new \stdClass(), new \stdClass()];

        $this->handler->remove($entities);

        \Phake::verify($this->delegate)
            ->remove($entities)
        ;
    }

    public function testGetCount(): void
    {
        \Phake::when($this->delegate)
            ->getCount('foo', ['bar'])
            ->thenReturn(777)
        ;

        $result = $this->handler->getCount('foo', ['bar']);

        $this->assertEquals(777, $result);
    }
}

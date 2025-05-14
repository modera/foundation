<?php

namespace Modera\BackendTranslationsToolBundle\Tests\Unit\Filtering;

use Modera\BackendTranslationsToolBundle\Filtering\Filter;
use Modera\ServerCrudBundle\Persistence\DoctrineRegistryPersistenceHandler;
use Modera\ServerCrudBundle\Persistence\OperationResult;
use Modera\ServerCrudBundle\Persistence\PersistenceHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FilterTest extends \PHPUnit\Framework\TestCase
{
    private ContainerInterface $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = \Phake::mock(ContainerInterface::class);
        \Phake::when($this->container)->get(DoctrineRegistryPersistenceHandler::class)->thenReturn(new DummyDoctrinePersistenceHandler());
        \Phake::when($this->container)->get('doctrine.orm.entity_manager')->thenReturn(new DummyDoctrineEntityManager());
    }

    private function filterCheck($item, $id, $name): void
    {
        $this->assertInstanceOf('Modera\BackendTranslationsToolBundle\Filtering\FilterInterface', $item);
        $this->assertEquals($id, $item->getId());
        $this->assertEquals($name, $item->getName());
        $this->assertEquals(true, $item->isAllowed());

        $result = $item->getResult([]);
        $this->assertTrue(\is_array($result));

        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);

        $this->assertArrayHasKey('total', $result);
        $this->assertEquals(0, $result['total']);

        $this->assertArrayHasKey('items', $result);
        $this->assertTrue(\is_array($result['items']));
    }

    public function testAllTranslationTokensFilter(): void
    {
        $item = new Filter\AllTranslationTokensFilter($this->container);
        $this->filterCheck($item, 'all', 'All');
    }

    public function testNewTranslationTokensFilter(): void
    {
        $item = new Filter\NewTranslationTokensFilter($this->container);
        $this->filterCheck($item, 'new', 'New');
    }

    public function testObsoleteTranslationTokensFilter(): void
    {
        $item = new Filter\ObsoleteTranslationTokensFilter($this->container);
        $this->filterCheck($item, 'obsolete', 'Obsolete');
    }
}

class DummyDoctrinePersistenceHandler implements PersistenceHandlerInterface
{
    public function getCount(string $className, array $params): int
    {
        return 0;
    }

    public function query(string $className, array $params): array
    {
        return [];
    }

    public function resolveEntityPrimaryKeyFields(string $entityClass): array
    {
        return [];
    }

    public function save(object $entity): OperationResult
    {
        return new OperationResult();
    }

    public function update(object $entity): OperationResult
    {
        return new OperationResult();
    }

    public function updateBatch(array $entities): OperationResult
    {
        return new OperationResult();
    }

    public function remove(array $entities): OperationResult
    {
        return new OperationResult();
    }
}

class DummyDoctrineEntityManager
{
    public function createQuery()
    {
        return new DummyDoctrineQuery();
    }
}

class DummyDoctrineQuery
{
    public function getResult()
    {
        return [];
    }
}

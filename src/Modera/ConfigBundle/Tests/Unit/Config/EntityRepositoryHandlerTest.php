<?php

namespace Modera\ConfigBundle\Tests\Unit\Config;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Modera\ConfigBundle\Config\EntityRepositoryHandler;
use Modera\ConfigBundle\Entity\ConfigurationEntry;

class DummyEntity
{
    public ?string $value = null;

    public ?string $id = null;

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function getId(): ?string
    {
        return $this->id;
    }
}

class EntityRepositoryHandlerTest extends \PHPUnit\Framework\TestCase
{
    private ConfigurationEntry $ce;
    private EntityManagerInterface $em;
    private EntityRepository $entityRepository;
    private EntityRepositoryHandler $handler;

    public function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->entityRepository = $this->createMock(EntityRepository::class);
        $this->ce = $this->createMock(ConfigurationEntry::class);
        $this->handler = new EntityRepositoryHandler($this->em);
    }

    private function teachConfigEntryToReturnServerHandlerConfig(array $overrideConfig = []): void
    {
        $cfg = \array_merge([
            'entityFqcn' => DummyEntity::class,
            'toStringMethodName' => 'getValue',
            'clientValueMethodName' => 'getId',
        ], $overrideConfig);
        $this->ce->expects($this->atLeastOnce())
             ->method('getServerHandlerConfig')
             ->will($this->returnValue($cfg));
    }

    private function teachConfigEntryToReturnClientValue($clientValue): void
    {
        $this->ce->expects($this->atLeastOnce())
             ->method('getDenormalizedValue')
             ->will($this->returnValue($clientValue));
    }

    private function teachEntityManagerToExpectForFind($id, $entityInstance): void
    {
        $this->entityRepository
            ->expects($this->any())
            ->method('find')
            ->with($this->equalTo($id))
            ->will($this->returnValue($entityInstance))
        ;

        $this->em
            ->expects($this->any())
            ->method('getRepository')
            ->with($this->equalTo(DummyEntity::class))
            ->will($this->returnValue($this->entityRepository))
        ;
    }

    public function testGetReadableValue(): void
    {
        $id = 10;
        $entityInstance = new DummyEntity();
        $entityInstance->value = 'foobar';

        $this->teachConfigEntryToReturnServerHandlerConfig();
        $this->teachConfigEntryToReturnClientValue($id);
        $this->teachEntityManagerToExpectForFind($id, $entityInstance);

        $this->assertEquals($entityInstance->value, $this->handler->getReadableValue($this->ce));
    }

    public function testGetValue(): void
    {
        $id = 50;
        $entityInstance = new DummyEntity();

        $this->teachConfigEntryToReturnServerHandlerConfig();
        $this->teachConfigEntryToReturnClientValue($id);
        $this->teachEntityManagerToExpectForFind($id, $entityInstance);

        $this->assertSame($entityInstance, $this->handler->getValue($this->ce));
    }

    public function testConvertToStorageValue(): void
    {
        $this->teachConfigEntryToReturnServerHandlerConfig();

        $entity = new DummyEntity();
        $entity->id = 'foo';
        $this->assertEquals($entity->id, $this->handler->convertToStorageValue($entity, $this->ce));
    }

    public function testConvertToStorageValueWithNewClientValueMethodName(): void
    {
        $this->teachConfigEntryToReturnServerHandlerConfig([
            'clientValueMethodName' => 'getValue',
        ]);

        $entity = new DummyEntity();
        $entity->value = 'foo';
        $this->assertEquals($entity->value, $this->handler->convertToStorageValue($entity, $this->ce));
    }
}

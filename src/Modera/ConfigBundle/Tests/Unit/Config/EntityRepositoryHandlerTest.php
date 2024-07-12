<?php

namespace Modera\ConfigBundle\Tests\Unit\Config;

use Modera\ConfigBundle\Entity\ConfigurationEntry;
use Modera\ConfigBundle\Config\EntityRepositoryHandler;

class DummyEntity
{
    public $value;

    public $id;

    public function getValue()
    {
        return $this->value;
    }

    public function getId()
    {
        return $this->id;
    }
}

/**
 * @author Sergei Lissovski <sergei.lissovski@modera.org>
 */
class EntityRepositoryHandlerTest extends \PHPUnit\Framework\TestCase
{
    private $ce;
    private $em;
    private $entityRepository;
    /* @var EntityRepositoryHandler $handler */
    private $handler;

    public function setUp(): void
    {
        $this->em = $this->createMock('Doctrine\ORM\EntityManager', array(), array(), '', false);
        $this->entityRepository = $this->createMock('Doctrine\ORM\EntityRepository', array(), array(), '', false);
        $this->ce = $this->createMock(ConfigurationEntry::class, array(), array(), '', false);
        $this->handler = new EntityRepositoryHandler($this->em);
    }

    private function teachConfigEntryToReturnServerHandlerConfig(array $overrideConfig = array())
    {
        $cfg = array_merge(array(
            'entityFqcn' => DummyEntity::class,
            'toStringMethodName' => 'getValue',
            'clientValueMethodName' => 'getId',
        ), $overrideConfig);
        $this->ce->expects($this->atLeastOnce())
             ->method('getServerHandlerConfig')
             ->will($this->returnValue($cfg));
    }

    private function teachConfigEntryToReturnClientValue($clientValue)
    {
        $this->ce->expects($this->atLeastOnce())
             ->method('getDenormalizedValue')
             ->will($this->returnValue($clientValue));
    }

    private function teachEntityManagerToExpectForFind($id, $entityInstance)
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

    public function testGetReadableValue()
    {
        $id = 10;
        $entityInstance = new DummyEntity();
        $entityInstance->value = 'foobar';

        $this->teachConfigEntryToReturnServerHandlerConfig();
        $this->teachConfigEntryToReturnClientValue($id);
        $this->teachEntityManagerToExpectForFind($id, $entityInstance);

        $this->assertEquals($entityInstance->value, $this->handler->getReadableValue($this->ce));
    }

    public function testGetValue()
    {
        $id = 50;
        $entityInstance = new DummyEntity();

        $this->teachConfigEntryToReturnServerHandlerConfig();
        $this->teachConfigEntryToReturnClientValue($id);
        $this->teachEntityManagerToExpectForFind($id, $entityInstance);

        $this->assertSame($entityInstance, $this->handler->getValue($this->ce));
    }

    public function testConvertToStorageValue()
    {
        $this->teachConfigEntryToReturnServerHandlerConfig();

        $entity = new DummyEntity();
        $entity->id = 'foo';
        $this->assertEquals($entity->id, $this->handler->convertToStorageValue($entity, $this->ce));
    }

    public function testConvertToStorageValueWithNewClientValueMethodName()
    {
        $this->teachConfigEntryToReturnServerHandlerConfig(array(
            'clientValueMethodName' => 'getValue',
        ));

        $entity = new DummyEntity();
        $entity->value = 'foo';
        $this->assertEquals($entity->value, $this->handler->convertToStorageValue($entity, $this->ce));
    }
}

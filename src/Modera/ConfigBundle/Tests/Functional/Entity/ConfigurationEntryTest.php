<?php

namespace Modera\ConfigBundle\Tests\Functional\Entity;

use Doctrine\ORM\Tools\SchemaTool;
use Modera\ConfigBundle\Config\HandlerInterface;
use Modera\ConfigBundle\Config\ValueUpdatedHandlerInterface;
use Modera\ConfigBundle\Entity\ConfigurationEntry;
use Modera\ConfigBundle\Tests\Fixtures\Entities\User;
use Modera\FoundationBundle\Testing\FunctionalTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ConfigurationEntryTest extends FunctionalTestCase
{
    private static SchemaTool $st;

    public static function doSetUpBeforeClass(): void
    {
        self::$st = new SchemaTool(self::$em);
        self::$st->createSchema([
            self::$em->getClassMetadata(ConfigurationEntry::class),
            self::$em->getClassMetadata(User::class),
        ]);
    }

    public static function doTearDownAfterClass(): void
    {
        self::$st->dropSchema([
            self::$em->getClassMetadata(ConfigurationEntry::class),
            self::$em->getClassMetadata(User::class),
        ]);
    }

    public function testSetClientValueAndGetClientValue(): void
    {
        $em = self::$em;

        $intEntry = new ConfigurationEntry('entry1');
        $this->assertEquals('entry1', $intEntry->getName());
        $this->assertEquals(ConfigurationEntry::TYPE_INT, $intEntry->setDenormalizedValue(123));
        $this->assertEquals(123, $intEntry->getDenormalizedValue());

        $stringValue = new ConfigurationEntry('entry2');
        $this->assertEquals(ConfigurationEntry::TYPE_STRING, $stringValue->setDenormalizedValue('blahblah'));
        $this->assertEquals('blahblah', $stringValue->getDenormalizedValue());

        $textValue = new ConfigurationEntry('entry3');
        $this->assertEquals(ConfigurationEntry::TYPE_TEXT, $textValue->setDenormalizedValue(str_repeat('foo', 100)));
        $this->assertEquals(str_repeat('foo', 100), $textValue->getDenormalizedValue());

        $arrayValue = new ConfigurationEntry('entry4');
        $this->assertEquals(ConfigurationEntry::TYPE_ARRAY, $arrayValue->setDenormalizedValue(['foo']));
        $this->assertSame(['foo'], $arrayValue->getDenormalizedValue());

        $floatValue = new ConfigurationEntry('entry5');
        $this->assertEquals(ConfigurationEntry::TYPE_FLOAT, $floatValue->setDenormalizedValue(1.2345));
        $this->assertEquals(1.2345, $floatValue->getDenormalizedValue());

        $floatValue2 = new ConfigurationEntry('entry6');
        $this->assertEquals(ConfigurationEntry::TYPE_FLOAT, $floatValue2->setDenormalizedValue(0.009));
        $this->assertEquals(0.009, $floatValue2->getDenormalizedValue());

        $boolValue = new ConfigurationEntry('entry7');
        $this->assertEquals(ConfigurationEntry::TYPE_BOOL, $boolValue->setDenormalizedValue(true));
        $this->assertTrue(true === $boolValue->getDenormalizedValue());

        foreach ([$intEntry, $stringValue, $textValue, $arrayValue, $floatValue, $floatValue2, $boolValue] as $ce) {
            $em->persist($ce);
            $em->flush();
            $this->assertNotNull($intEntry->getId());
        }

        $em->clear();

        /** @var ConfigurationEntry $floatValue2 */
        $floatValue2 = self::$em->find(ConfigurationEntry::class, $floatValue2->getId());
        $this->assertEquals(ConfigurationEntry::TYPE_FLOAT, $floatValue2->getSavedAs());
        $this->assertTrue(is_float($floatValue2->getValue()));
        $this->assertEquals(0.009, $floatValue2->getValue());
    }

    public function testInitialization(): void
    {
        $ce = new ConfigurationEntry('greeting_msg');
        $ce->setDenormalizedValue('hello world');

        $em = self::$em;
        $em->persist($ce);
        $em->flush();
        $em->getUnitOfWork()->clear();

        $ce = $em->getRepository(ConfigurationEntry::class)->findOneBy([
            'name' => 'greeting_msg',
        ]);
        $this->assertInstanceOf(ConfigurationEntry::class, $ce);
        $this->assertInstanceOf(ContainerInterface::class, $ce->getContainer());
    }

    private function createMockContainer($handlerId, $handlerInstance): ContainerInterface
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->atLeastOnce())
            ->method('get')
            ->with($this->equalTo($handlerId))
            ->will($this->returnValue($handlerInstance));

        return $container;
    }

    public function testGetValue(): void
    {
        $handlerServiceId = 'foo_handler';
        $expectedValue = 'jfksdljfdks';

        $handler = $this->createMock(HandlerInterface::class);
        $handler->expects($this->atLeastOnce())
            ->method('getValue')
            ->with($this->isInstanceOf(ConfigurationEntry::class))
            ->will($this->returnValue($expectedValue));

        $container = $this->createMockContainer($handlerServiceId, $handler);

        $ce = new ConfigurationEntry('bar_prop');
        $ce->setServerHandlerConfig([
            'handler' => $handlerServiceId,
        ]);
        $ce->init($container);
        $ce->setDenormalizedValue('foo_val');

        $this->assertEquals($expectedValue, $ce->getValue());
    }

    public function testSetValue(): void
    {
        $handlerServiceId = 'bar_handler';

        $clientValue = 'foo bar baz';
        $convertedValue = 'converted foo bar baz';

        $handler = $this->createMock(HandlerInterface::class);
        $handler->expects($this->atLeastOnce())
               ->method('convertToStorageValue')
               ->with($this->equalTo($clientValue), $this->isInstanceOf(ConfigurationEntry::class))
               ->will($this->returnValue($convertedValue));

        $container = $this->createMockContainer($handlerServiceId, $handler);

        $ce = new ConfigurationEntry('bar_prop');
        $ce->setServerHandlerConfig([
            'handler' => $handlerServiceId,
        ]);
        $ce->init($container);

        $ce->setValue($clientValue);
        $this->assertEquals($convertedValue, $ce->getDenormalizedValue());
    }

    public function testUpdateHandler(): void
    {
        $id = 'update_handler';

        $handler = $this->createMock(ValueUpdatedHandlerInterface::class);
        $container = $this->createMockContainer($id, $handler);

        $ce = new ConfigurationEntry('foo_prop');
        $ce->setServerHandlerConfig([
            'update_handler' => $id,
        ]);
        $ce->init($container);
        $ce->setValue('foo');

        self::$em->persist($ce);
        self::$em->flush();
        $ce->init($container);

        $handler->expects($this->atLeastOnce())
            ->method('onUpdate')
            ->with($this->equalTo($ce));

        $ce->setValue('bar');

        self::$em->flush();
    }

    public function testSetClientValueWithBadValue(): void
    {
        $this->expectException(\RuntimeException::class);
        $ce = new ConfigurationEntry('blah');
        $ce->setDenormalizedValue(new \stdClass());
    }
}

<?php

namespace Modera\ConfigBundle\Tests\Unit\Config;

use Modera\ConfigBundle\Config\BooleanHandler;
use Modera\ConfigBundle\Entity\ConfigurationEntry;

class BooleanHandlerTest extends \PHPUnit\Framework\TestCase
{
    private ConfigurationEntry $entry;

    private BooleanHandler $handler;

    public function setUp(): void
    {
        $this->entry = $this->createMock(ConfigurationEntry::class);
        $this->handler = new BooleanHandler();
    }

    public function testGetReadableValueWithNoConfigAnd1IsReturned(): void
    {
        $this->entry->expects($this->once())
                    ->method('getDenormalizedValue')
                    ->will($this->returnValue(1));

        $this->assertEquals('true', $this->handler->getReadableValue($this->entry));
    }

    public function testGetReadableValueWithNoConfigAnd0IsReturned(): void
    {
        $this->entry->expects($this->once())
            ->method('getDenormalizedValue')
            ->will($this->returnValue(0));

        $this->assertEquals('false', $this->handler->getReadableValue($this->entry));
    }

    private function createEntryWithServerConfig($clientValue, array $config): ConfigurationEntry
    {
        $entry = $this->createMock(ConfigurationEntry::class);

        $entry->expects($this->once())
              ->method('getDenormalizedValue')
              ->will($this->returnValue(1));

        $entry->expects($this->atLeastOnce())
              ->method('getServerHandlerConfig')
              ->will($this->returnValue($config));

        return $entry;
    }

    public function testGetReadableValueWithConfig(): void
    {
        $this->assertEquals(
            'Aye!',
            $this->handler->getReadableValue($this->createEntryWithServerConfig(1, ['true_text' => 'Aye!']))
        );

        $this->assertEquals(
            'Nein!',
            $this->handler->getReadableValue($this->createEntryWithServerConfig(0, ['true_text' => 'Nein!']))
        );
    }

    public function testGetValue(): void
    {
        $this->entry->expects($this->once())
             ->method('getDenormalizedValue')
             ->will($this->returnValue('serverValue'));

        $this->assertEquals('serverValue', $this->handler->getValue($this->entry));
    }

    public function testConvertToStorageValue(): void
    {
        $this->assertTrue(false === $this->handler->convertToStorageValue('xxx', $this->entry));
        $this->assertTrue(true === $this->handler->convertToStorageValue(1, $this->entry));
        $this->assertTrue(true === $this->handler->convertToStorageValue('1', $this->entry));
        $this->assertTrue(true === $this->handler->convertToStorageValue(true, $this->entry));
        $this->assertTrue(true === $this->handler->convertToStorageValue('true', $this->entry));
    }
}

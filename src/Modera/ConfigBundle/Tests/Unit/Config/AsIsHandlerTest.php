<?php

namespace Modera\ConfigBundle\Tests\Unit\Config;

use Modera\ConfigBundle\Config\AsIsHandler;
use Modera\ConfigBundle\Entity\ConfigurationEntry;

class AsIsHandlerTest extends \PHPUnit\Framework\TestCase
{
    private ConfigurationEntry $entry;

    private AsIsHandler $handler;

    public function setUp(): void
    {
        $this->entry = $this->createMock(ConfigurationEntry::class);
        $this->handler = new AsIsHandler();
    }

    public function testGetReadableValue(): void
    {
        $this->entry->expects($this->once())
             ->method('getDenormalizedValue')
             ->will($this->returnValue('clientValue'));

        $this->assertEquals('clientValue', $this->handler->getReadableValue($this->entry));
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
        $this->assertEquals('xxx', $this->handler->convertToStorageValue('xxx', $this->entry));
    }
}

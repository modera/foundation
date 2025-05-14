<?php

namespace Modera\ConfigBundle\Tests\Unit\Config;

use Modera\ConfigBundle\Config\DictionaryHandler;
use Modera\ConfigBundle\Entity\ConfigurationEntry;

class DictionaryHandlerTest extends \PHPUnit\Framework\TestCase
{
    private ConfigurationEntry $entry;

    private DictionaryHandler $handler;

    public function setUp(): void
    {
        $this->handler = new DictionaryHandler();

        $config = [
            'dictionary' => [
                'foo' => 'foo-val',
            ],
        ];
        $this->entry = $this->createMock(ConfigurationEntry::class);
        $this->entry
             ->expects($this->any())
             ->method('getServerHandlerConfig')
             ->will($this->returnValue($config));
    }

    public function testGetReadableValue(): void
    {
        $this->entry
             ->expects($this->atLeastOnce())
             ->method('getDenormalizedValue')
             ->will($this->returnValue('foo'));

        $this->assertEquals('foo-val', $this->handler->getReadableValue($this->entry));
    }

    public function testGetValue(): void
    {
        $this->entry
             ->expects($this->atLeastOnce())
             ->method('getDenormalizedValue')
             ->will($this->returnValue('mega-value'));

        $this->assertEquals('mega-value', $this->handler->getValue($this->entry));
    }

    public function testConvertToStorageValue(): void
    {
        $this->assertEquals('blah', $this->handler->convertToStorageValue('blah', $this->entry));
    }
}

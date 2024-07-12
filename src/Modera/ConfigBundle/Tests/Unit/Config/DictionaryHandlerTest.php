<?php

namespace Modera\ConfigBundle\Tests\Unit\Config;

use Modera\ConfigBundle\Config\DictionaryHandler;
use Modera\ConfigBundle\Entity\ConfigurationEntry;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class DictionaryHandlerTest extends \PHPUnit\Framework\TestCase
{
    private $entry;
    /* @var DictionaryHandler */
    private $handler;

    public function setUp(): void
    {
        $this->handler = new DictionaryHandler();

        $config = array(
            'dictionary' => array(
                'foo' => 'foo-val',
            ),
        );
        $this->entry = $this->createMock(
            ConfigurationEntry::class,
            array(),
            array(),
            '',
            null,
            false
        );
        $this->entry
             ->expects($this->any())
             ->method('getServerHandlerConfig')
             ->will($this->returnValue($config));
    }

    public function testGetReadableValue()
    {
        $this->entry
             ->expects($this->atLeastOnce())
             ->method('getDenormalizedValue')
             ->will($this->returnValue('foo'));

        $this->assertEquals('foo-val', $this->handler->getReadableValue($this->entry));
    }

    public function testGetValue()
    {
        $this->entry
             ->expects($this->atLeastOnce())
             ->method('getDenormalizedValue')
             ->will($this->returnValue('mega-value'));

        $this->assertEquals('mega-value', $this->handler->getValue($this->entry));
    }

    public function testConvertToStorageValue()
    {
        $this->assertEquals('blah', $this->handler->convertToStorageValue('blah', $this->entry));
    }
}

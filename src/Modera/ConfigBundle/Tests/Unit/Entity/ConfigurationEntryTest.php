<?php

namespace Modera\ConfigBundle\Tests\Unit\Entity;

use Modera\ConfigBundle\Entity\ConfigurationEntry;

class ConfigurationEntryTest extends \PHPUnit\Framework\TestCase
{
    public function testSetName(): void
    {
        $ce = new ConfigurationEntry('foo');

        $this->assertEquals('foo', $ce->getName());

        $ce->setName('bar');

        $this->assertEquals('bar', $ce->getName());
    }
}

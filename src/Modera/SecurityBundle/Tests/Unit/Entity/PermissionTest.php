<?php

namespace Modera\SecurityBundle\Tests\Unit\Entity;

use Modera\SecurityBundle\Model\Permission;

class PermissionTest extends \PHPUnit\Framework\TestCase
{
    public function testConstructorAndGetters()
    {
        $p = new Permission('foo name', 'FOO_ROLE', 'foo_category', 'bar description');

        $this->assertEquals('foo name', $p->getName());
        $this->assertEquals('FOO_ROLE', $p->getRole());
        $this->assertEquals('foo_category', $p->getCategory());
        $this->assertEquals('bar description', $p->getDescription());
    }
}

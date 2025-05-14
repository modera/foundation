<?php

namespace Modera\SecurityBundle\Tests\Unit\Model;

use Modera\SecurityBundle\Model\PermissionCategory;

class PermissionCategoryTest extends \PHPUnit\Framework\TestCase
{
    public function testConstructorAndGetters(): void
    {
        $pc = new PermissionCategory('foo name', 'foo_name');

        $this->assertEquals('foo name', $pc->getName());
        $this->assertEquals('foo_name', $pc->getTechnicalName());
    }
}

<?php

namespace Modera\SecurityBundle\Tests\Unit\Entity;

use Modera\SecurityBundle\Entity\Group;

class GroupTest extends \PHPUnit\Framework\TestCase
{
    public function testNormalizeRefName(): void
    {
        $this->assertEquals('QWERTY', Group::normalizeRefName('qwerty'));
        $this->assertEquals('QT', Group::normalizeRefName('!1q34%^&* ~@342T'));
    }
}

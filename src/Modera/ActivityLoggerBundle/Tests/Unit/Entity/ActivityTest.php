<?php

namespace Modera\ActivityLoggerBundle\Tests\Unit\Entity;

use Modera\ActivityLoggerBundle\Entity\Activity;

class ActivityTest extends \PHPUnit\Framework\TestCase
{
    public function testConstructor(): void
    {
        $a = new Activity();

        $this->assertInstanceOf('DateTime', $a->getCreatedAt());
    }
}

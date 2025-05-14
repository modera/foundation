<?php

namespace Modera\FoundationBundle\Tests\Unit\Testing;

use Modera\FoundationBundle\Testing\FunctionalTestCase;

class FunctionalTestCaseTest extends \PHPUnit\Framework\TestCase
{
    public function testCreateKernel(): void
    {
        global $_SERVER;

        $reflClass = new \ReflectionClass(FunctionalTestCase::class);

        $reflMethod = $reflClass->getMethod('createKernel');
        $reflMethod->setAccessible(true);

        $instance1 = $reflMethod->invoke(null);
        $instance2 = $reflMethod->invoke(null);

        if (isset($_SERVER['MONOLITH_TEST_SUITE'])) {
            $this->assertNotSame($instance1, $instance2);
        } else {
            $this->assertSame($instance1, $instance2);
        }
    }
}

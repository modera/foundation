<?php

namespace Modera\BackendDashboardBundle\Tests\Unit\Dashboard;

use Modera\BackendDashboardBundle\Dashboard\SimpleDashboard;

/**
 * @copyright 2013 Modera Foundation
 * @author Alex Rudakov <alexandr.rudakov@modera.net>
 */
class SimpleDashboardTest extends \PHPUnit\Framework\TestCase
{
    public function testConstructor()
    {
        $dashboard = new SimpleDashboard('foo', 'bar', 'baz');

        $this->assertEquals('foo', $dashboard->getName());
        $this->assertEquals('bar', $dashboard->getLabel());
        $this->assertEquals('baz', $dashboard->getUiClass());

        $this->assertTrue($dashboard->isAllowed(
                \Phake::mock('Symfony\Component\DependencyInjection\ContainerInterface')
            ));
    }
}

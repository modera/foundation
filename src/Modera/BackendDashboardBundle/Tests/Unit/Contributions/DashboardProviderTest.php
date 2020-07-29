<?php

namespace Modera\BackendDashboardBundle\Tests\Unit\Contributions;

use Modera\BackendDashboardBundle\Contributions\DashboardProvider;
use Modera\BackendDashboardBundle\Dashboard\DashboardInterface;

/**
 * @author    Alex Rudakov <alexandr.rudakov@modera.org>
 * @copyright 2014 Modera Foundation
 */
class DashboardProviderTest extends \PHPUnit\Framework\TestCase
{
    public function testItems()
    {
        $provider = new DashboardProvider();

        $items = $provider->getItems();

        foreach ($items as $item) {
            $this->assertInstanceOf(DashboardInterface::class, $item);
        }
    }
}

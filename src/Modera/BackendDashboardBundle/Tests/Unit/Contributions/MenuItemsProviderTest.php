<?php

namespace Modera\BackendDashboardBundle\Tests\Unit\Contributions;

use Modera\BackendDashboardBundle\Contributions\MenuItemsProvider;
use Modera\MjrIntegrationBundle\Menu\MenuItem;

/**
 * @author    Alex Rudakov <alexandr.rudakov@modera.org>
 * @copyright 2014 Modera Foundation
 */
class MenuItemsProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testItems()
    {
        $provider = new MenuItemsProvider();

        $items = $provider->getItems();

        $this->assertEquals(1, count($items));

        $this->assertInstanceOf(MenuItem::class, $items[0]);
    }

    public function testOrder()
    {
        $provider = new MenuItemsProvider();
        $this->assertTrue(is_int($provider->getOrder()));
    }
}

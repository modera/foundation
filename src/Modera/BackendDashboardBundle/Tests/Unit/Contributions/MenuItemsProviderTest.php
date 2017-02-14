<?php

namespace Modera\BackendDashboardBundle\Tests\Unit\Contributions;

use Modera\MjrIntegrationBundle\Menu\MenuItem;
use Modera\BackendDashboardBundle\Contributions\MenuItemsProvider;
use Modera\BackendDashboardBundle\Contributions\ConfigMergersProvider;

/**
 * @author    Alex Rudakov <alexandr.rudakov@modera.org>
 * @copyright 2014 Modera Foundation
 */
class MenuItemsProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigMergersProvider
     */
    private $provider;

    protected function setUp()
    {
        $this->provider = \Phake::mock(ConfigMergersProvider::class);

        \Phake::when($this->provider)
            ->getUserLandingSection()
            ->thenReturn('dashboard')
        ;
    }

    public function testGetItems()
    {
        $provider = new MenuItemsProvider($this->provider);

        $items = $provider->getItems();

        $this->assertEquals(1, count($items));

        $this->assertInstanceOf(MenuItem::class, $items[0]);
    }

    public function testOrder()
    {
        $provider = new MenuItemsProvider($this->provider);

        $this->assertTrue(is_int($provider->getOrder()));
    }

    /**
     * @group MPFE-975
     */
    public function testGetItems_whenLandingIsNotDashboard()
    {
        $p = \Phake::mock(ConfigMergersProvider::class);

        \Phake::when($p)
            ->getUserLandingSection()
            ->thenReturn('foo')
        ;

        $provider = new MenuItemsProvider($p);

        $this->assertEquals(0, count($provider->getItems()));
    }
}

<?php

namespace Modera\BackendLanguagesBundle\Tests\Unit\Contributions;

use Modera\BackendLanguagesBundle\Contributions\RoutingResourcesProvider;

class RoutingResourcesProviderTest extends \PHPUnit\Framework\TestCase
{
    public function testGetItems(): void
    {
        $provider = new RoutingResourcesProvider();

        $items = $provider->getItems();

        $this->assertTrue(\is_array($items));
        $this->assertEquals(1, \count($items));
    }
}

<?php

namespace Modera\MJRCacheAwareClassLoaderBundle\Tests\Unit\Contributions;

use Modera\MJRCacheAwareClassLoaderBundle\Contributions\RoutingResourcesProvider;

class RoutingResourcesProviderTest extends \PHPUnit\Framework\TestCase
{
    public function testGetItems(): void
    {
        $provider = new RoutingResourcesProvider();

        $result = $provider->getItems();

        $this->assertTrue(\is_array($result));
        $this->assertTrue(\in_array('@ModeraMJRCacheAwareClassLoaderBundle/Resources/config/routing.yaml', $result));
    }
}

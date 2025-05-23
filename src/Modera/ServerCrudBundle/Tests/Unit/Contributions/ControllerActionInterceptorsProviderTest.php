<?php

namespace Modera\ServerCrudBundle\Tests\Unit\Contributions;

use Modera\ServerCrudBundle\Contributions\ControllerActionInterceptorsProvider;
use Modera\ServerCrudBundle\Security\SecurityControllerActionsInterceptor;

class ControllerActionInterceptorsProviderTest extends \PHPUnit\Framework\TestCase
{
    public function testGetItems(): void
    {
        $ac = $this->createMock('Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface');

        $provider = new ControllerActionInterceptorsProvider($ac);

        $items = $provider->getItems();

        $this->assertEquals(1, \count($items));
        $this->assertInstanceOf(SecurityControllerActionsInterceptor::class, $items[0]);

        $items2 = $provider->getItems();

        // interceptors must be created only once
        $this->assertSame($items, $items2);
    }
}

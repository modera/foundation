<?php

namespace Modera\SecurityBundle\Tests\Functional\DependencyInjection;

use Modera\FoundationBundle\Testing\FunctionalTestCase;
use Modera\SecurityBundle\RootUserHandling\RootUserHandlerInterface;

class ModeraSecurityExtensionTest extends FunctionalTestCase
{
    public function testHowWellHandlerAliasIsEstablished(): void
    {
        $handler = self::getContainer()->get(RootUserHandlerInterface::class);

        $this->assertInstanceOf(
            RootUserHandlerInterface::class,
            $handler,
        );
    }
}

<?php

namespace Modera\SecurityBundle\Tests\Unit\EventListener;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Modera\SecurityBundle\Entity\User;
use Modera\SecurityBundle\EventListener\RootUserHandlerInjectionListener;
use Modera\SecurityBundle\RootUserHandling\RootUserHandlerInterface;

class RootUserHandlerInjectionListenerTest extends \PHPUnit\Framework\TestCase
{
    private RootUserHandlerInterface $rootUserHandler;

    public function setUp(): void
    {
        $this->rootUserHandler = \Phake::mock(RootUserHandlerInterface::class);
    }

    private function createEvent($object = null): LifecycleEventArgs
    {
        $event = \Phake::mock(LifecycleEventArgs::class);

        \Phake::when($event)
            ->getObject()
            ->thenReturn($object)
        ;

        return $event;
    }

    public function testPostLoadWithEntity(): void
    {
        $user = \Phake::mock(User::class);

        $event = $this->createEvent($user);

        $listener = new RootUserHandlerInjectionListener($this->rootUserHandler);
        $listener->postLoad($user, $event);

        \Phake::verify($user)->init($this->rootUserHandler);
    }
}

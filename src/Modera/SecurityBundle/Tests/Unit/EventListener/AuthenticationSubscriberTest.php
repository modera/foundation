<?php

namespace Modera\SecurityBundle\Tests\Unit\EventListener;

use Modera\SecurityBundle\Entity\User;
use Modera\SecurityBundle\EventListener\AuthenticationSubscriber;

class AuthenticationSubscriberTest extends \PHPUnit\Framework\TestCase
{
    private function createAuthenticationSubscriber(): AuthenticationSubscriber
    {
        $om = \Phake::mock('Doctrine\Persistence\ObjectManager');
        $user = \Phake::mock(User::class);
        $doctrine = \Phake::mock('Doctrine\Persistence\ManagerRegistry');

        \Phake::when($om)->persist($user)->thenReturn(null);
        \Phake::when($om)->flush()->thenReturn(null);
        \Phake::when($doctrine)->getManager()->thenReturn($om);

        return new AuthenticationSubscriber($doctrine);
    }

    public function testUserStateChangeOnAuthenticationSuccess(): void
    {
        $user = new User();
        $subscriber = $this->createAuthenticationSubscriber();

        $event = \Phake::mock('Symfony\Component\Security\Core\Event\AuthenticationEvent');
        $token = \Phake::mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');

        \Phake::when($event)->getAuthenticationToken()->thenReturn($token);
        \Phake::when($token)->getUser()->thenReturn($user);

        $this->assertSame(User::STATE_NEW, $user->getState());
        $this->assertNull($user->getLastLogin());
        $subscriber->onAuthenticationSuccess($event);
        $this->assertSame(User::STATE_ACTIVE, $user->getState());
        $this->assertInstanceOf(\DateTime::class, $user->getLastLogin());
    }
}

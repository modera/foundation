<?php

namespace Modera\SecurityBundle\Tests\Unit\Service;

use Modera\SecurityBundle\Entity\User;
use Modera\SecurityBundle\Service\UserService;

class UserServiceTest extends \PHPUnit\Framework\TestCase
{
    public function testRemove(): void
    {
        $this->expectException(\RuntimeException::class);

        $em = \Phake::mock('Doctrine\ORM\EntityManager');
        $rootUserHandler = \Phake::mock('Modera\SecurityBundle\RootUserHandling\RootUserHandlerInterface');

        $user = \Phake::mock(User::class);

        \Phake::when($rootUserHandler)->isRootUser($user)->thenReturn(true);

        $service = new UserService($em, $rootUserHandler);
        $service->remove($user);
    }

    public function testDisableRootUser(): void
    {
        $this->expectException(\RuntimeException::class);

        $em = \Phake::mock('Doctrine\ORM\EntityManager');
        $rootUserHandler = \Phake::mock('Modera\SecurityBundle\RootUserHandling\RootUserHandlerInterface');

        $user = \Phake::mock(User::class);

        \Phake::when($rootUserHandler)->isRootUser($user)->thenReturn(true);

        $service = new UserService($em, $rootUserHandler);
        $service->disable($user);
    }

    public function testDisableEnableUser(): void
    {
        $em = \Phake::mock('Doctrine\ORM\EntityManager');
        $rootUserHandler = \Phake::mock('Modera\SecurityBundle\RootUserHandling\RootUserHandlerInterface');

        $service = new UserService($em, $rootUserHandler);

        $user = new User();
        $user->setActive(true);
        \Phake::when($rootUserHandler)->isRootUser($user)->thenReturn(false);

        $service->disable($user);
        $this->assertFalse($user->isActive());

        $service->enable($user);
        $this->assertTrue($user->isActive());
    }

    public function testFind(): void
    {
        $em = \Phake::mock('Doctrine\ORM\EntityManager');
        $repo = \Phake::mock('Doctrine\Persistence\ObjectRepository');
        $rootUserHandler = \Phake::mock('Modera\SecurityBundle\RootUserHandling\RootUserHandlerInterface');
        $service = new UserService($em, $rootUserHandler);

        \Phake::when($em)->getRepository(User::class)->thenReturn($repo);

        $user1 = \Phake::mock(User::class);
        \Phake::when($user1)->getId()->thenReturn(1);
        \Phake::when($user1)->getGender()->thenReturn(User::GENDER_MALE);
        $user2 = \Phake::mock(User::class);
        \Phake::when($user2)->getId()->thenReturn(2);
        \Phake::when($user2)->getGender()->thenReturn(User::GENDER_FEMALE);
        $user3 = \Phake::mock(User::class);
        \Phake::when($user3)->getId()->thenReturn(3);
        \Phake::when($user3)->getGender()->thenReturn(User::GENDER_MALE);

        \Phake::when($repo)->findOneBy(['id' => 0])->thenReturn(null);
        $this->assertNull($service->findUserBy('id', 0));

        \Phake::when($repo)->findOneBy(['id' => 1])->thenReturn($user1);
        $this->assertEquals($user1, $service->findUserBy('id', 1));

        \Phake::when($repo)->findBy(['gender' => User::GENDER_MALE])->thenReturn([$user1, $user3]);
        $this->assertEquals([$user1, $user3], $service->findUsersBy('gender', User::GENDER_MALE));
    }

    public function testRootUser(): void
    {
        $em = \Phake::mock('Doctrine\ORM\EntityManager');
        $rootUserHandler = \Phake::mock('Modera\SecurityBundle\RootUserHandling\RootUserHandlerInterface');
        $service = new UserService($em, $rootUserHandler);

        $user = \Phake::mock(User::class);
        \Phake::when($rootUserHandler)->getUser()->thenReturn($user);
        \Phake::when($rootUserHandler)->isRootUser($user)->thenReturn(true);

        $this->assertEquals($user, $service->getRootUser());
        $this->assertTrue($service->isRootUser($user));
    }
}

<?php

namespace Modera\SecurityBundle\Tests\Functional\Service;

use Doctrine\ORM\Tools\SchemaTool;
use Modera\FoundationBundle\Testing\FunctionalTestCase;
use Modera\SecurityBundle\Entity\Group;
use Modera\SecurityBundle\Entity\Permission;
use Modera\SecurityBundle\Entity\PermissionCategory as PermissionCategoryEntity;
use Modera\SecurityBundle\Entity\User;
use Modera\SecurityBundle\Service\UserService;

class UserServiceTest extends FunctionalTestCase
{
    private static SchemaTool $st;

    public static function doSetUpBeforeClass(): void
    {
        self::$st = new SchemaTool(self::$em);
        self::$st->createSchema([
            self::$em->getClassMetadata(User::class),
            self::$em->getClassMetadata(Group::class),
            self::$em->getClassMetadata(Permission::class),
            self::$em->getClassMetadata(PermissionCategoryEntity::class),
        ]);
    }

    public static function doTearDownAfterClass(): void
    {
        self::$st->dropSchema([
            self::$em->getClassMetadata(User::class),
            self::$em->getClassMetadata(Group::class),
            self::$em->getClassMetadata(Permission::class),
            self::$em->getClassMetadata(PermissionCategoryEntity::class),
        ]);
    }

    public function testGetByRole(): void
    {
        $user1 = new User();
        $user2 = new User();
        $user3 = new User();

        $user1->setUsername('user1');
        $user1->setPassword('pwd1');
        $user1->setEmail('user1@email.test');

        $user2->setUsername('user2');
        $user2->setPassword('pwd2');
        $user2->setEmail('user2@email.test');

        $user3->setUsername('user3');
        $user3->setPassword('pwd3');
        $user3->setEmail('user3@email.test');

        $permission1 = new Permission();
        $permission2 = new Permission();

        $permission1->setRoleName('ROLE_USER');
        $permission1->addUser($user1);

        $permission2->setRoleName('ROLE_ADMIN');
        $permission2->addUser($user2);

        $group1 = new Group();
        $group2 = new Group();

        $group1->setName('User');
        $group1->addPermission($permission1);
        $group1->addUser($user3);

        $group2->setName('Admin');
        $group2->addPermission($permission2);
        $group2->addUser($user3);

        self::$em->persist($user1);
        self::$em->persist($user2);
        self::$em->persist($user3);

        self::$em->persist($permission1);
        self::$em->persist($permission2);

        self::$em->persist($group1);
        self::$em->persist($group2);

        self::$em->flush();

        $rootUserHandler = \Phake::mock('Modera\SecurityBundle\RootUserHandling\RootUserHandlerInterface');
        $service = new UserService(self::$em, $rootUserHandler);

        $this->assertEquals([$user1, $user3], $service->getUsersByRole('ROLE_USER'));
        $this->assertEquals([$user2, $user3], $service->getUsersByRole('ROLE_ADMIN'));

        $this->assertEquals([$user1->getId(), $user3->getId()], $service->getIdsByRole('ROLE_USER'));
        $this->assertEquals([$user2->getId(), $user3->getId()], $service->getIdsByRole('ROLE_ADMIN'));
    }
}

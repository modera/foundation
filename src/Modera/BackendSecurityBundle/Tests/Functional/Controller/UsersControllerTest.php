<?php

namespace Modera\BackendSecurityBundle\Tests\Functional\Controller;

use Doctrine\ORM\Tools\SchemaTool;
use Modera\ActivityLoggerBundle\Entity\Activity;
use Modera\BackendSecurityBundle\Controller\UsersController;
use Modera\FoundationBundle\Testing\FunctionalTestCase;
use Modera\SecurityBundle\Entity\Group;
use Modera\SecurityBundle\Entity\Permission;
use Modera\SecurityBundle\Entity\PermissionCategory;
use Modera\SecurityBundle\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class UsersControllerTest extends FunctionalTestCase
{
    private static SchemaTool $schemaTool;

    private static UserPasswordHasherInterface $passwordHasher;

    private static User $user;

    public static function doSetUpBeforeClass(): void
    {
        static::$schemaTool = new SchemaTool(static::$em);
        static::$schemaTool->dropSchema(static::getTablesMetadata());
        static::$schemaTool->createSchema(static::getTablesMetadata());

        static::$passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        static::$user = new User();
        static::$user->setEmail('test@test.com');
        static::$user->setUsername('testUser');
        static::$user->setPassword(
            static::$passwordHasher->hashPassword(static::$user, '1234')
        );

        $entityPermissionCategory = new PermissionCategory();
        $entityPermissionCategory->setName('backend_user');
        $entityPermissionCategory->setTechnicalName('backend_user');
        static::$em->persist($entityPermissionCategory);

        $entityPermission = new Permission();
        $entityPermission->setRoleName('IS_AUTHENTICATED_FULLY');
        $entityPermission->setDescription('IS_AUTHENTICATED_FULLY');
        $entityPermission->setName('IS_AUTHENTICATED_FULLY');
        $entityPermission->setCategory($entityPermissionCategory);

        $entityPermission2 = new Permission();
        $entityPermission2->setRoleName('ROLE_MANAGE_PERMISSIONS');
        $entityPermission2->setDescription('ROLE_MANAGE_PERMISSIONS');
        $entityPermission2->setName('ROLE_MANAGE_PERMISSIONS');
        $entityPermission2->setCategory($entityPermissionCategory);

        $entityPermission3 = new Permission();
        $entityPermission3->setRoleName('ROLE_ACCESS_BACKEND_TOOLS_SECURITY_SECTION');
        $entityPermission3->setDescription('ROLE_ACCESS_BACKEND_TOOLS_SECURITY_SECTION');
        $entityPermission3->setName('ROLE_ACCESS_BACKEND_TOOLS_SECURITY_SECTION');
        $entityPermission3->setCategory($entityPermissionCategory);

        $entityPermission4 = new Permission();
        $entityPermission4->setRoleName('ROLE_MANAGE_USER_PROFILES');
        $entityPermission4->setDescription('ROLE_MANAGE_USER_PROFILES');
        $entityPermission4->setName('ROLE_MANAGE_USER_PROFILES');
        $entityPermission4->setCategory($entityPermissionCategory);

        $entityPermission5 = new Permission();
        $entityPermission5->setRoleName('ROLE_MANAGE_USER_ACCOUNTS');
        $entityPermission5->setDescription('ROLE_MANAGE_USER_ACCOUNTS');
        $entityPermission5->setName('ROLE_MANAGE_USER_ACCOUNTS');
        $entityPermission5->setCategory($entityPermissionCategory);

        static::$em->persist($entityPermission);
        static::$em->persist($entityPermission2);
        static::$em->persist($entityPermission3);
        static::$em->persist($entityPermission4);
        static::$em->persist($entityPermission5);
        static::$em->flush();

        $group = new Group();
        $group->setRefName('BACKEND-USER');
        $group->setName('backend-user');
        $group->addPermission($entityPermission);
        $group->addPermission($entityPermission2);
        $group->addPermission($entityPermission3);
        $group->addPermission($entityPermission4);
        $group->addPermission($entityPermission5);

        static::$user->addToGroup($group);

        static::$em->persist($group);
        static::$em->persist(static::$user);

        static::$em->flush();
    }

    public function testListAction(): void
    {
        $controller = $this->getController();
        $response = $controller->listAction([
            'hydration' => [
                'profile' => 'list',
            ],
            'page' => 1,
            'start' => 0,
            'limit' => 25,
        ]);

        $this->assertArrayHasKey('success', $response);
        $this->assertArrayHasKey('total', $response);
        $this->assertArrayHasKey('items', $response);

        // assuming this is first test in file
        $this->assertGreaterThanOrEqual(1, \count($response['items']));

        $hydratedUser = $response['items'][0];
        $this->assertArrayHasKey('id', $hydratedUser);
        $this->assertArrayHasKey('username', $hydratedUser);
        $this->assertArrayHasKey('email', $hydratedUser);
        $this->assertArrayHasKey('firstName', $hydratedUser);
        $this->assertArrayHasKey('lastName', $hydratedUser);
        $this->assertArrayHasKey('middleName', $hydratedUser);
        $this->assertArrayHasKey('isActive', $hydratedUser);
        $this->assertArrayHasKey('state', $hydratedUser);
        $this->assertArrayHasKey('lastLogin', $hydratedUser);
        $this->assertArrayHasKey('groups', $hydratedUser);
        $this->assertArrayHasKey('permissions', $hydratedUser);
        $this->assertArrayHasKey('meta', $hydratedUser);
        $this->assertCount(1, $hydratedUser['groups']);
    }

    public function testCreateAction(): User
    {
        $params = [
            'record' => [
                'id' => '',
                'firstName' => 'John',
                'lastName' => 'Doe',
                'email' => 'john.doe@test.com',
                'username' => 'john.doe',
            ],
        ];

        $controller = $this->getController();
        $response = $controller->createAction($params);

        $this->assertTrue($response['success']);

        /** @var User[] $userList */
        $userList = static::$em->getRepository(User::class)->findAll();
        $lastUser = \array_pop($userList);

        $this->assertEquals($params['record']['firstName'], $lastUser->getFirstName());
        $this->assertEquals($params['record']['lastName'], $lastUser->getLastName());
        $this->assertEquals($params['record']['email'], $lastUser->getEmail());
        $this->assertEquals($params['record']['username'], $lastUser->getUsername());
        $this->assertArrayHasKey('modera_security', $lastUser->getMeta());
        $this->assertArrayHasKey('used_passwords', $lastUser->getMeta()['modera_security']);

        return $lastUser;
    }

    /**
     * @depends testCreateAction
     */
    public function testUpdateAction(User $user): void
    {
        $params = [
            'record' => [
                'id' => $user->getId(),
                'firstName' => 'Homer',
                'lastName' => 'Simpson',
                'email' => 'homer.simpson@test.com',
                'username' => 'homer.simpson',
            ],
        ];

        $controller = $this->getController();
        $response = $controller->updateAction($params);

        $this->assertTrue($response['success']);

        /** @var User $userFromDb */
        $userFromDb = static::$em->getRepository(User::class)->find($user->getId());

        $this->assertEquals($params['record']['firstName'], $userFromDb->getFirstName());
        $this->assertEquals($params['record']['lastName'], $userFromDb->getLastName());
        $this->assertEquals($params['record']['email'], $userFromDb->getEmail());
        $this->assertEquals($params['record']['username'], $userFromDb->getUsername());
    }

    public function doSetUp(): void
    {
        $token = new UsernamePasswordToken(static::$user, 'secured_area', static::$user->getRoles());

        static::getContainer()->get('security.token_storage')->setToken($token);
    }

    private function getController(): UsersController
    {
        return static::getContainer()->get(UsersController::class);
    }

    private static function getTablesClasses(): array
    {
        return [
            Permission::class,
            PermissionCategory::class,
            User::class,
            Group::class,
            Activity::class,
        ];
    }

    private static function getTablesMetadata(): array
    {
        $metaData = [];

        foreach (static::getTablesClasses() as $class) {
            $metaData[] = static::$em->getClassMetadata($class);
        }

        return $metaData;
    }

    protected static function getIsolationLevel(): string
    {
        return self::IM_CLASS;
    }
}

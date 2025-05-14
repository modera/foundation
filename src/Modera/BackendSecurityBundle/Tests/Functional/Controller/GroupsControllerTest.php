<?php

namespace Modera\BackendSecurityBundle\Tests\Functional\Controller;

use Doctrine\ORM\Tools\SchemaTool;
use Modera\BackendSecurityBundle\Controller\GroupsController;
use Modera\FoundationBundle\Testing\FunctionalTestCase;
use Modera\SecurityBundle\Entity\Group;
use Modera\SecurityBundle\Entity\Permission;
use Modera\SecurityBundle\Entity\PermissionCategory;
use Modera\SecurityBundle\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class GroupsControllerTest extends FunctionalTestCase
{
    private static SchemaTool $schemaTool;

    private static UserPasswordHasherInterface $passwordHasher;

    private static User $user;

    private static GroupsController $controller;

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

        static::$em->persist($entityPermission);
        static::$em->persist($entityPermission2);
        static::$em->persist($entityPermission3);
        static::$em->flush();

        $group = new Group();
        $group->setRefName('BACKEND-USER');
        $group->setName('backend-user');
        $group->addPermission($entityPermission);
        $group->addPermission($entityPermission2);
        $group->addPermission($entityPermission3);

        static::$user->addToGroup($group);

        static::$em->persist($group);
        static::$em->persist(static::$user);

        static::$em->flush();

        static::$controller = static::getContainer()->get(GroupsController::class);
    }

    public function doSetUp(): void
    {
        $token = new UsernamePasswordToken(static::$user, 'secured_area', static::$user->getRoles());

        static::getContainer()->get('security.token_storage')->setToken($token);
    }

    /**
     * Simple correct behavior group create.
     */
    public function testCreateAction(): ?Group
    {
        $beforeGroupsCount = \count(static::$em->getRepository(Group::class)->findAll());

        $params = [
            'record' => [
                'id' => '',
                'name' => 'testName',
                'refName' => 'testRefName',
            ],
        ];

        $result = static::$controller->createAction($params);

        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('created_models', $result);
        $this->assertArrayHasKey('modera.security_bundle.group', $result['created_models']);
        $this->assertCount(1, $result['created_models']['modera.security_bundle.group']);

        $afterGroupsCount = \count(static::$em->getRepository(Group::class)->findAll());
        $this->assertEquals($beforeGroupsCount + 1, $afterGroupsCount);

        $createdGroup = static::$em->getRepository(Group::class)->find($result['created_models']['modera.security_bundle.group'][0]);

        $this->assertEquals('testName', $createdGroup->getName());
        $this->assertEquals('TESTREFNAME', $createdGroup->getRefName());

        return $createdGroup;
    }

    /**
     * @depends testCreateAction
     */
    public function testCreateActionEmptyName(): void
    {
        $params = [
            'record' => [
                'id' => '',
                'name' => '',
                'refName' => '',
            ],
        ];

        $result = static::$controller->createAction($params);

        $this->assertArrayHasKey('success', $result);
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('field_errors', $result);
        $this->assertCount(1, $result['field_errors']);
        $this->assertArrayHasKey('name', $result['field_errors']);
    }

    /**
     * @depends testCreateAction
     */
    public function testCreateActionDuplicatedRefName(): void
    {
        $params = [
            'record' => [
                'id' => '',
                'name' => 'testName2',
                'refName' => 'testRefName',
            ],
        ];

        $result = static::$controller->createAction($params);

        $this->assertArrayHasKey('success', $result);
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('field_errors', $result);
        $this->assertCount(1, $result['field_errors']);
        $this->assertArrayHasKey('refName', $result['field_errors']);
    }

    /**
     * @depends testCreateAction
     */
    public function testUpdateAction(Group $group): void
    {
        $params = [
            'record' => [
                'id' => $group->getId(),
                'name' => 'testNameUpdated',
                'refName' => 'testRefNameUpdated',
            ],
        ];

        $result = static::$controller->updateAction($params);

        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('updated_models', $result);
        $this->assertArrayHasKey('modera.security_bundle.group', $result['updated_models']);
        $this->assertCount(1, $result['updated_models']['modera.security_bundle.group']);
        $this->assertEquals($group->getId(), $result['updated_models']['modera.security_bundle.group'][0]);

        /** @var Group $groupFromDb */
        $groupFromDb = static::$em->find(Group::class, $group->getId());

        $this->assertEquals('testNameUpdated', $groupFromDb->getName());
        $this->assertEquals('TESTREFNAMEUPDATED', $groupFromDb->getRefName());
    }

    /**
     * @depends testCreateAction
     * @depends testUpdateAction
     */
    public function testUpdateActionSameRefName(Group $group): Group
    {
        $this->assertEquals('TESTREFNAMEUPDATED', $group->getRefName());

        $params = [
            'record' => [
                'id' => $group->getId(),
                'name' => 'newTestName',
                'refName' => 'testRefNameUpdated',
            ],
        ];

        $result = static::$controller->updateAction($params);

        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('updated_models', $result);
        $this->assertArrayHasKey('modera.security_bundle.group', $result['updated_models']);
        $this->assertCount(1, $result['updated_models']['modera.security_bundle.group']);
        $this->assertEquals($group->getId(), $result['updated_models']['modera.security_bundle.group'][0]);

        /** @var Group $groupFromDb */
        $groupFromDb = static::$em->find(Group::class, $group->getId());

        $this->assertEquals('newTestName', $groupFromDb->getName());
        $this->assertEquals('TESTREFNAMEUPDATED', $groupFromDb->getRefName());

        return $groupFromDb;
    }

    /**
     * @depends testUpdateActionSameRefName
     */
    public function testUpdateActionExistingRefNameUse(Group $group): void
    {
        $newGroup = new Group();
        $newGroup->setName('brandNewGroup');
        $newGroup->setRefName('brandNewRefName');

        static::$em->persist($newGroup);
        static::$em->flush();

        $this->assertEquals('TESTREFNAMEUPDATED', $group->getRefName());

        $params = [
            'record' => [
                'id' => $group->getId(),
                'name' => 'newTestNameExistingRef',
                'refName' => 'brandNewRefName',
            ],
        ];

        $result = static::$controller->updateAction($params);

        $this->assertArrayHasKey('success', $result);
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('field_errors', $result);
        $this->assertCount(1, $result['field_errors']);
        $this->assertArrayHasKey('refName', $result['field_errors']);
    }

    private static function getTablesClasses(): array
    {
        return [
            Permission::class,
            PermissionCategory::class,
            User::class,
            Group::class,
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

<?php

namespace Modera\SecurityBundle\Tests\Functional\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Tools\SchemaTool;
use Modera\FoundationBundle\Testing\FunctionalTestCase;
use Modera\SecurityBundle\Entity\Group;
use Modera\SecurityBundle\Entity\Permission;
use Modera\SecurityBundle\Entity\PermissionCategory as PermissionCategoryEntity;
use Modera\SecurityBundle\Entity\User;

class GroupRepositoryTest extends FunctionalTestCase
{
    private static SchemaTool $st;

    public static function doSetUpBeforeClass(): void
    {
        static::$st = new SchemaTool(static::$em);
        static::$st->dropSchema(static::getTableClassesMetadata());
        static::$st->createSchema(static::getTableClassesMetadata());
    }

    public static function doTearDownAfterClass(): void
    {
        static::$st->dropSchema(static::getTableClassesMetadata());
    }

    public function testFindByRefName(): Group
    {
        $emptyGroupList = static::$em->getRepository(Group::class)->findByRefName('test');
        $this->assertCount(0, $emptyGroupList);

        $group = new Group();
        $group->setName('test');
        $group->setRefName('test');

        static::$em->persist($group);
        static::$em->flush();

        $oneGroupList = static::$em->getRepository(Group::class)->findByRefName('test');
        $this->assertCount(1, $oneGroupList);
        $this->assertEquals($group, $oneGroupList[0]);

        $anotherEmptyList = static::$em->getRepository(Group::class)->findByRefName('testNew');
        $this->assertCount(0, $anotherEmptyList);

        return $group;
    }

    /**
     * There is unique constrain present on refName field. And this constrain is NOT case-sensitive.
     * So findByRefName search is NOT case-sensitive.
     *
     * @depends testFindByRefName
     */
    public function testFindByRefNameCases(Group $group): void
    {
        $oneGroupList = static::$em->getRepository(Group::class)->findByRefName('Test');
        $this->assertCount(1, $oneGroupList);
        $this->assertEquals($group, $oneGroupList[0]);

        $anotherOneGroupList = static::$em->getRepository(Group::class)->findByRefName('tesT');
        $this->assertCount(1, $anotherOneGroupList);
        $this->assertEquals($group, $anotherOneGroupList[0]);

        $lastOneGroupList = static::$em->getRepository(Group::class)->findByRefName('TEST');
        $this->assertCount(1, $lastOneGroupList);
        $this->assertEquals($group, $lastOneGroupList[0]);
    }

    protected static function getIsolationLevel(): string
    {
        return static::IM_CLASS;
    }

    /**
     * Db Tables used in test.
     */
    private static function getTableClasses(): array
    {
        return [
            User::class,
            Group::class,
            Permission::class,
            PermissionCategoryEntity::class,
        ];
    }

    /**
     * @return ClassMetadata[]
     */
    private static function getTableClassesMetadata(): array
    {
        $metaData = [];
        foreach (static::getTableClasses() as $class) {
            $metaData[] = static::$em->getClassMetadata($class);
        }

        return $metaData;
    }
}

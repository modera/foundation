<?php

namespace Modera\SecurityBundle\Tests\Functional\DataInstallation;

use Doctrine\ORM\Tools\SchemaTool;
use Modera\FoundationBundle\Testing\FunctionalTestCase;
use Modera\SecurityBundle\Entity\Group;
use Modera\SecurityBundle\Entity\Permission;
use Modera\SecurityBundle\Entity\PermissionCategory;
use Modera\SecurityBundle\Entity\User;

abstract class AbstractTestCase extends FunctionalTestCase
{
    private static SchemaTool $st;

    protected static function getIsolationLevel(): string
    {
        return static::IM_CLASS;
    }

    public static function doSetUpBeforeClass(): void
    {
        self::$st = new SchemaTool(self::$em);
        self::$st->createSchema([
            self::$em->getClassMetadata(User::class),
            self::$em->getClassMetadata(Group::class),
            self::$em->getClassMetadata(Permission::class),
            self::$em->getClassMetadata(PermissionCategory::class),
        ]);
    }

    public static function doTearDownAfterClass(): void
    {
        self::$st->dropSchema([
            self::$em->getClassMetadata(User::class),
            self::$em->getClassMetadata(Group::class),
            self::$em->getClassMetadata(Permission::class),
            self::$em->getClassMetadata(PermissionCategory::class),
        ]);
    }
}

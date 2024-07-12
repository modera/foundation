<?php

namespace Modera\ConfigBundle\Tests\Functional;

use Doctrine\ORM\Tools\SchemaTool;
use Modera\ConfigBundle\Entity\ConfigurationEntry;
use Modera\ConfigBundle\Tests\Fixtures\Entities\User;
use Modera\FoundationBundle\Testing\FunctionalTestCase;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
class AbstractFunctionalTestCase extends FunctionalTestCase
{
    /**
     * @var SchemaTool
     */
    private static $st;

    public static function doSetUpBeforeClass(): void
    {
        self::$st = new SchemaTool(self::$em);
        self::$st->createSchema([
            self::$em->getClassMetadata(ConfigurationEntry::class),
            self::$em->getClassMetadata(User::class),
        ]);
    }

    public static function doTearDownAfterClass(): void
    {
        self::$st->dropSchema([
            self::$em->getClassMetadata(ConfigurationEntry::class),
            self::$em->getClassMetadata(User::class),
        ]);
    }
}

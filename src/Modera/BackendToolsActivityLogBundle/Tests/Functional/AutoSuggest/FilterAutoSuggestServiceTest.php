<?php

namespace Modera\BackendToolsActivityLogBundle\Tests\Functional\AutoSuggest;

use Doctrine\ORM\Tools\SchemaTool;
use Modera\ActivityLoggerBundle\Entity\Activity;
use Modera\ActivityLoggerBundle\Manager\ActivityManagerInterface;
use Modera\BackendToolsActivityLogBundle\AutoSuggest\FilterAutoSuggestService;
use Modera\FoundationBundle\Testing\FunctionalTestCase;
use Modera\SecurityBundle\Entity\Group;
use Modera\SecurityBundle\Entity\Permission;
use Modera\SecurityBundle\Entity\PermissionCategory;
use Modera\SecurityBundle\Entity\User;

class FilterAutoSuggestServiceTest extends FunctionalTestCase
{
    private FilterAutoSuggestService $s;

    private static SchemaTool $st;

    // override
    public static function doSetUpBeforeClass(): void
    {
        self::$st = new SchemaTool(self::$em);
        self::$st->createSchema([
            self::$em->getClassMetadata(Activity::class),
            self::$em->getClassMetadata(User::class),
            self::$em->getClassMetadata(Group::class),
            self::$em->getClassMetadata(Permission::class),
            self::$em->getClassMetadata(PermissionCategory::class),
        ]);
    }

    // override
    public static function doTearDownAfterClass(): void
    {
        self::$st->dropSchema([
            self::$em->getClassMetadata(Activity::class),
            self::$em->getClassMetadata(User::class),
            self::$em->getClassMetadata(Group::class),
            self::$em->getClassMetadata(Permission::class),
            self::$em->getClassMetadata(PermissionCategory::class),
        ]);
    }

    public function doSetUp(): void
    {
        $this->s = self::getContainer()->get(FilterAutoSuggestService::class);
    }

    public function testServiceExists(): void
    {
        $this->assertInstanceOf(FilterAutoSuggestService::class, $this->s);
    }

    private function createUser(): User
    {
        $u = new User();
        $u->setFirstName('Joe');
        $u->setLastName('Doe');
        $u->setUsername('djatel');
        $u->setEmail('djatel@23example1.com');
        $u->setPassword(1234);

        self::$em->persist($u);
        self::$em->flush();

        return $u;
    }

    public function testSuggestForUser(): void
    {
        $u = $this->createUser();

        $result = $this->s->suggest('user', 'ate');

        // $this->assertTrue(is_array($result));
        // $this->assertEquals(0, count($result));

        // /** @var ActivityManagerInterface $activityMgr */
        // $activityMgr = self::getContainer()->get('modera_activity_logger.manager.activity_manager');
        // $activityMgr->info('some message', array(
        //     'type' => 'dat_foox_type',
        //     'author' => $u->getId()
        // ));

        $this->assertTrue(\is_array($result));
        $this->assertEquals(1, \count($result));
        $this->assertTrue(\is_array($result[0]));
        $this->assertArrayHasKey('id', $result[0]);
        $this->assertEquals($u->getId(), $result[0]['id']);
        $this->assertArrayHasKey('value', $result[0]);
        $this->assertEquals(sprintf('%s (%s)', $u->getFullName(), $u->getUsername()), $result[0]['value']);
    }

    public function testSuggestEvent(): void
    {
        /** @var ActivityManagerInterface $activityMgr */
        $activityMgr = self::getContainer()->get('modera_activity_logger.manager.activity_manager');
        $activityMgr->info('some message', [
            'type' => 'dat_foox_type',
        ]);

        $result = $this->s->suggest('eventType', 'foox');

        $this->assertTrue(\is_array($result));
        $this->assertEquals(1, \count($result));
        $this->assertArrayHasKey('id', $result[0]);
        $this->assertArrayHasKey('value', $result[0]);
        $this->assertEquals('dat_foox_type', $result[0]['id']);
        $this->assertEquals('dat_foox_type', $result[0]['value']);
    }

    public function testSuggestExact(): void
    {
        $u = $this->createUser();

        $result = $this->s->suggest('exact-user', $u->getId());

        $this->assertTrue(\is_array($result));
        $this->assertEquals(1, \count($result));
        $this->assertArrayHasKey('id', $result[0]);
        $this->assertArrayHasKey('value', $result[0]);
        $this->assertEquals($u->getId(), $result[0]['id']);
        $this->assertEquals(sprintf('%s (%s)', $u->getFullName(), $u->getUsername()), $result[0]['value']);
    }
}

<?php

namespace Modera\ActivityLoggerBundle\Tests\Functional;

use Doctrine\ORM\Tools\SchemaTool;
use Modera\ActivityLoggerBundle\Entity\Activity;
use Modera\ActivityLoggerBundle\Manager\DoctrineOrmActivityManager;
use Modera\FoundationBundle\Testing\FunctionalTestCase;
use Psr\Log\LogLevel;

class DoctrineOrmActivityManagerTest extends FunctionalTestCase
{
    private DoctrineOrmActivityManager $mgr;

    private static SchemaTool $st;

    // override
    public static function doSetUpBeforeClass(): void
    {
        self::$st = new SchemaTool(self::$em);
        self::$st->createSchema([self::$em->getClassMetadata(Activity::class)]);
    }

    // override
    public static function doTearDownAfterClass(): void
    {
        self::$st->dropSchema([self::$em->getClassMetadata(Activity::class)]);
    }

    // override
    public function doSetUp(): void
    {
        $this->mgr = self::getContainer()->get(DoctrineOrmActivityManager::class);
    }

    private function getLastCreatedActivity(): Activity
    {
        $query = self::$em->createQuery(\sprintf('SELECT a FROM %s a ORDER BY a.id DESC', Activity::class));
        $query->setMaxResults(1);

        return $query->getSingleResult();
    }

    public function testLog(): void
    {
        $cx = [
            'author' => 'Joe',
            'type' => 'foo_type',
            'meta' => ['foo', 'bar'],
        ];

        $this->mgr->log(LogLevel::ALERT, 'testing it', $cx);

        $activity = $this->getLastCreatedActivity();

        $this->assertNotNull($activity);
        $this->assertEquals(LogLevel::ALERT, $activity->getLevel());
        $this->assertEquals('testing it', $activity->getMessage());
        $this->assertEquals('Joe', $activity->getAuthor());
        $this->assertEquals('foo_type', $activity->getType());
        $this->assertSame($cx['meta'], $activity->getMeta());
    }

    public function testQuery(): void
    {
        $activity = new Activity();
        $activity->setType('foo_type');
        $activity->setLevel('debug');
        $activity->setMessage('foo message');

        self::$em->persist($activity);
        self::$em->flush();

        $result = $this->mgr->query([
            'filter' => [
                ['property' => 'type', 'value' => 'eq:'.$activity->getType()],
            ],
        ]);

        $this->assertTrue(\is_array($result));
        $this->assertArrayHasKey('items', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertEquals(1, $result['total']);
        $this->assertEquals(1, \count($result['items']));
        $this->assertInstanceOf(Activity::class, $result['items'][0]);
        $this->assertSame($activity->getId(), $result['items'][0]->getId());
    }
}

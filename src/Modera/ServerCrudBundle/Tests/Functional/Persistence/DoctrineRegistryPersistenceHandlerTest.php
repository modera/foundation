<?php

namespace Modera\ServerCrudBundle\Tests\Functional\Persistence;

use Modera\ServerCrudBundle\Persistence\DoctrineRegistryPersistenceHandler;
use Modera\ServerCrudBundle\Persistence\OperationResult;
use Modera\ServerCrudBundle\Persistence\PersistenceHandlerInterface;
use Modera\ServerCrudBundle\Tests\Functional\AbstractTestCase;
use Modera\ServerCrudBundle\Tests\Functional\DummyNote;
use Modera\ServerCrudBundle\Tests\Functional\DummyUser;

class DoctrineRegistryPersistenceHandlerTest extends AbstractTestCase
{
    protected function getHandler(): PersistenceHandlerInterface
    {
        return self::getContainer()->get(DoctrineRegistryPersistenceHandler::class);
    }

    public function testServiceExistence(): void
    {
        $this->assertInstanceOf(DoctrineRegistryPersistenceHandler::class, $this->getHandler());
    }

    public function testSave(): void
    {
        $repository = self::$em->getRepository(DummyUser::class);

        $this->assertEquals(0, \count($repository->findAll()));

        $user = new DummyUser();
        $user->firstname = 'Vassily';
        $user->lastname = 'Pupkin';

        $result = $this->getHandler()->save($user);

        $this->assertInstanceOf(OperationResult::class, $result);
        $this->assertEquals(1, \count($result->getCreatedEntities()));

        $this->assertNotNull($user->id);

        $this->assertEquals(1, \count($repository->findAll()));

        $fetchedUser = $repository->find($user->getId());

        $this->assertInstanceOf(DummyUser::class, $fetchedUser);
        $this->assertSame($user->firstname, $fetchedUser->firstname);
        $this->assertSame($user->lastname, $fetchedUser->lastname);
    }

    public function testUpdate(): void
    {
        $user = new DummyUser();
        $user->firstname = 'Vassily';
        $user->lastname = 'Pupkin';

        self::$em->persist($user);
        self::$em->flush();

        $user->lastname = 'Blah';

        $result = $this->getHandler()->update($user);

        $updatedEntities = $result->getUpdatedEntities();
        $this->assertEquals(1, \count($updatedEntities));
        $this->assertArrayHasKey(0, $updatedEntities);
        $this->assertArrayHasKey('entity_class', $updatedEntities[0]);
        $this->assertEquals(DummyUser::class, $updatedEntities[0]['entity_class']);
        $this->assertArrayHasKey('id', $updatedEntities[0]);
        $this->assertEquals($user->id, $updatedEntities[0]['id']);
    }

    /**
     * @return DummyUser[]
     */
    private function loadSomeData(): array
    {
        $users = [];

        for ($i = 0; $i < 10; ++$i) {
            $user = new DummyUser();
            $user->firstname = 'Vassily '.$i;
            $user->lastname = 'Pupkin '.$i;

            self::$em->persist($user);

            $users[] = $user;
        }
        self::$em->flush();

        return $users;
    }

    /**
     * @return DummyUser[]
     */
    private function loadSomeNotes(DummyUser $user): array
    {
        $notes = [];

        for ($i = 0; $i < 3; ++$i) {
            $note = new DummyNote();
            $note->text = 'Note_'.$i;
            $user->addNote($note);

            self::$em->persist($note);

            $notes[] = $note;
        }
        self::$em->flush();

        return $notes;
    }

    public function testQuery(): void
    {
        $query = [
            'page' => 2,
            'start' => 0,
            'limit' => 5,
        ];

        $users = $this->loadSomeData();

        /** @var DummyUser[] $result */
        $result = $this->getHandler()->query(DummyUser::class, $query);

        $this->assertTrue(\is_array($result));
        $this->assertEquals(5, \count($result));
        $this->assertEquals(8, $result[0]->id);

        foreach ($users as $user) {
            $this->loadSomeNotes($user);
        }

        $query['filter'] = [
            [
                [
                    'property' => 'notes.text',
                    'value' => 'like:%Note%',
                ],
            ],
        ];

        /** @var DummyUser[] $result */
        $result = $this->getHandler()->query(DummyUser::class, $query);

        $this->assertTrue(\is_array($result));
        $this->assertEquals(5, \count($result));
        $this->assertEquals(8, $result[0]->id);
    }

    public function testGetCount(): void
    {
        $query = [
            'page' => 2,
            'start' => 0,
            'limit' => 5,
        ];

        $this->assertEquals(0, $this->getHandler()->getCount(DummyUser::class, $query));

        $users = $this->loadSomeData();

        $this->assertEquals(10, $this->getHandler()->getCount(DummyUser::class, $query));

        foreach ($users as $user) {
            $this->loadSomeNotes($user);
        }

        $query['filter'] = [
            [
                [
                    'property' => 'notes.text',
                    'value' => 'like:%Note%',
                ],
            ],
        ];

        $this->assertEquals(10, $this->getHandler()->getCount(DummyUser::class, $query));
    }

    public function testRemove(): void
    {
        $users = $this->loadSomeData();

        $result = $this->getHandler()->remove($users);

        $this->assertInstanceOf(OperationResult::class, $result);
        $this->assertEquals(10, \count($result->getRemovedEntities()));

        foreach ($result->getRemovedEntities() as $entry) {
            $this->assertNull(self::$em->getRepository($entry['entity_class'])->find($entry['id']));
        }
    }

    public function testResolveEntityPrimaryKeyFields(): void
    {
        $fields = $this->getHandler()->resolveEntityPrimaryKeyFields(DummyUser::class);

        $this->assertTrue(\is_array($fields));
        $this->assertEquals(1, \count($fields));
        $this->assertTrue(\in_array('id', $fields));
    }

    public function testUpdateBatch(): void
    {
        $users = $this->loadSomeData();

        foreach ($users as $i => $user) {
            $user->firstname .= ''.$i;
        }

        $result = $this->getHandler()->updateBatch($users);

        $updatedEntities = $result->getUpdatedEntities();
        $this->assertEquals(count($users), \count($updatedEntities));

        self::$em->clear();

        $ids = [];
        foreach ($users as $user) {
            $ids[] = $user->id;
        }

        foreach ($users as $user) {
            $this->assertTrue(\in_array($user->id, $ids));

            $dbUser = self::$em->find(DummyUser::class, $user->id);

            $this->assertEquals($user->firstname, $dbUser->firstname);
        }
    }
}

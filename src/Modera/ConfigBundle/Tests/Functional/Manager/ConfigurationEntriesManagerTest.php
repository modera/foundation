<?php

namespace Modera\ConfigBundle\Tests\Functional\Manager;

use Modera\ConfigBundle\Entity\ConfigurationEntry;
use Modera\ConfigBundle\Manager\ConfigurationEntriesManager;
use Modera\ConfigBundle\Manager\ConfigurationEntriesManagerInterface;
use Modera\ConfigBundle\Tests\Fixtures\Entities\User;
use Modera\ConfigBundle\Tests\Functional\AbstractFunctionalTestCase;

class ConfigurationEntriesManagerTest extends AbstractFunctionalTestCase
{
    private function getManager(): ConfigurationEntriesManager
    {
        return self::getContainer()->get(ConfigurationEntriesManagerInterface::class);
    }

    public function testFindOneByName(): void
    {
        $vasya = new User('vasya');

        self::$em->persist($vasya);
        self::$em->flush();

        $ce1 = new ConfigurationEntry('cf_1');
        $ce1->setValue('foo');

        $this->getManager()->save($ce1);

        $foundCe1 = $this->getManager()->findOneByName('cf_1');

        $this->assertNotNull($foundCe1);
        $this->assertEquals($ce1->getId(), $foundCe1->getId());

        // ---

        $ce1->setOwner($vasya);

        $this->getManager()->save($ce1);

        $foundCe1 = $this->getManager()->findOneByName('cf_1');

        $this->assertNull(
            $foundCe1,
            'If ConfigurationEntry is already associated with owner but when invoking findOneByName() owner is not provided then nothing must be returned.'
        );

        // ---

        $foundCe1 = $this->getManager()->findOneByName('cf_1', $vasya);

        $this->assertNotNull($foundCe1);
        $this->assertEquals('cf_1', $foundCe1->getName());
    }

    public function testFindOneByNameOrDieNotFound(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->getManager()->findOneByNameOrDie('cf_1');
    }

    public function testFindOneByNameOrDie(): void
    {
        $vasya = new User('vasya');

        self::$em->persist($vasya);
        self::$em->flush();

        $ce1 = new ConfigurationEntry('cf_1');
        $ce1->setValue('foo');

        $this->getManager()->save($ce1);

        $ce = $this->getManager()->findOneByNameOrDie('cf_1');

        $this->assertNotNull($ce);
        $this->assertEquals('cf_1', $ce->getName());
    }

    public function testFindOneByNameOrDieNotFoundWithUserGiven(): void
    {
        $this->expectException(\RuntimeException::class);
        $vasya = new User('vasya');

        self::$em->persist($vasya);
        self::$em->flush();

        $this->getManager()->findOneByNameOrDie('cf_1', $vasya);
    }

    public function testFindOneByNameOrDieWithUserGiven(): void
    {
        $vasya = new User('vasya');

        self::$em->persist($vasya);
        self::$em->flush();

        $ce1 = new ConfigurationEntry('cf_1');
        $ce1->setValue('foo');
        $ce1->setOwner($vasya);

        $this->getManager()->save($ce1);

        $ce = $this->getManager()->findOneByNameOrDie('cf_1', $vasya);
        $this->assertNotNull($ce);
        $this->assertEquals('cf_1', $ce->getName());
    }

    public function testFindAllExposed(): void
    {
        $ce1 = new ConfigurationEntry('cf_1');
        $ce1->setValue('foo');

        $ce2 = new ConfigurationEntry('cf_2');
        $ce2->setValue('foo');

        $ce3 = new ConfigurationEntry('cf_3');
        $ce3->setValue('foo');
        $ce3->setExposed(false);

        $this->getManager()->save($ce1);
        $this->getManager()->save($ce2);
        $this->getManager()->save($ce3);

        $result = $this->getManager()->findAllExposed();

        $this->assertEquals(2, \count($result));
        $this->assertEquals('cf_1', $result[0]->getName());
        $this->assertEquals('cf_2', $result[1]->getName());

        // ---

        $vasya = new User('vasya');

        self::$em->persist($vasya);
        self::$em->flush();

        $ce1->setOwner($vasya);

        $this->getManager()->save($ce1);

        $result = $this->getManager()->findAllExposed();
        $this->assertEquals(1, \count($result));
        $this->assertEquals('cf_2', $result[0]->getName());

        // ---

        $result = $this->getManager()->findAllExposed($vasya);

        $this->assertEquals(1, \count($result));
        $this->assertEquals('cf_1', $result[0]->getName());
    }
}

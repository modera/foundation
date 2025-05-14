<?php

namespace Modera\ConfigBundle\Tests\Functional\Manager;

use Modera\ConfigBundle\Entity\ConfigurationEntry;
use Modera\ConfigBundle\Manager\UniquityValidator;
use Modera\ConfigBundle\Tests\Fixtures\Entities\User;
use Modera\ConfigBundle\Tests\Functional\AbstractFunctionalTestCase;

class UniquityValidatorTest extends AbstractFunctionalTestCase
{
    public function testIsValidForSavingWithoutOwner(): void
    {
        $ce1 = new ConfigurationEntry('cf_1');
        $ce1->setValue('foo');

        $uv = new UniquityValidator(self::$em, ['owner_entity' => null]);
        $this->assertTrue($uv->isValidForSaving($ce1));

        self::$em->persist($ce1);
        self::$em->flush();

        $this->assertTrue($uv->isValidForSaving($ce1));
    }

    public function testIsValidForSavingWithOwner(): void
    {
        $vasya = new User('vasya');

        self::$em->persist($vasya);
        self::$em->flush();

        $ce1 = new ConfigurationEntry('cf_1');
        $ce1->setValue('foo');
        $ce1->setOwner($vasya);

        $uv = new UniquityValidator(self::$em, ['owner_entity' => \get_class($vasya)]);
        $this->assertTrue($uv->isValidForSaving($ce1));

        self::$em->persist($ce1);
        self::$em->flush();

        $this->assertTrue($uv->isValidForSaving($ce1));
    }

    public function testIsValidForSavingChangeName(): void
    {
        $ce1 = new ConfigurationEntry('cf_1');
        $ce1->setValue('foo');

        $ce2 = new ConfigurationEntry('cf_2');
        $ce2->setValue('foo');

        self::$em->persist($ce1);
        self::$em->persist($ce2);
        self::$em->flush();

        $ce2->setName('cf_1');

        $uv = new UniquityValidator(self::$em, ['owner_entity' => null]);
        $this->assertFalse($uv->isValidForSaving($ce2));
    }

    public function testIsValidForSavingChangeNameWithOwner(): void
    {
        $vasya = new User('vasya');

        $ce1 = new ConfigurationEntry('cf_1');
        $ce1->setValue('foo');

        $ce2 = new ConfigurationEntry('cf_2');
        $ce2->setValue('foo');

        self::$em->persist($vasya);
        self::$em->persist($ce1);
        self::$em->persist($ce2);
        self::$em->flush();

        $ce2->setName('cf_1');

        $uv = new UniquityValidator(self::$em, ['owner_entity' => \get_class($vasya)]);
        $this->assertFalse($uv->isValidForSaving($ce2));
    }
}

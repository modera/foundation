<?php

namespace Modera\ConfigBundle\Tests\Unit\Manager;

use Doctrine\ORM\EntityManager;
use Modera\ConfigBundle\Entity\ConfigurationEntry;
use Modera\ConfigBundle\Manager\ConfigurationEntriesManager;
use Modera\ConfigBundle\Manager\ConfigurationEntryAlreadyExistsException;
use Modera\ConfigBundle\Manager\UniquityValidator;

class ConfigurationEntriesManagerTest extends \PHPUnit\Framework\TestCase
{
    public function testSave()
    {
        $this->expectException(ConfigurationEntryAlreadyExistsException::class);
        $entry = \Phake::mock(ConfigurationEntry::class);

        $em = \Phake::mock(EntityManager::class);

        // exception must be thrown when UniquityValidator said that given entry is not unique
        $uv = \Phake::mock(UniquityValidator::class);
        \Phake::when($uv)
            ->isValidForSaving($entry)
            ->thenReturn(false)
        ;

        $cem = new ConfigurationEntriesManager($em, [], $uv);

        $cem->save($entry);
    }

    public function testSaveNoUniquityValidatorGiven(): void
    {
        $entry = \Phake::mock(ConfigurationEntry::class);
        $em = \Phake::mock(EntityManager::class);

        $cem = new ConfigurationEntriesManager($em);

        $cem->save($entry);

        \Phake::verify($em)
            ->persist($entry)
        ;
        \Phake::verify($em)
            ->flush($entry)
        ;
    }
}

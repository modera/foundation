<?php

namespace Modera\ConfigBundle\Config;

use Sli\ExpanderBundle\Ext\ContributorInterface;
use Doctrine\ORM\EntityManager;
use Modera\ConfigBundle\Entity\ConfigurationEntry;

/**
 * Collects instances of {@class ConfigurationEntry} from the system and persists them to the database. If a
 * configuration entry already exists it won't be updated.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class ConfigEntriesInstaller
{
    private $provider;
    private $em;

    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @param ContributorInterface $provider
     * @param EntityManager        $em
     */
    public function __construct(ContributorInterface $provider, EntityManager $em)
    {
        $this->provider = $provider;
        $this->em = $em;
    }

    /**
     * @param ConfigurationEntryDefinition $entryDef
     * @return ConfigurationEntry|null
     */
    private function findEntry(ConfigurationEntryDefinition $entryDef)
    {
        return $this->em->getRepository(ConfigurationEntry::class)->findOneBy(array('name' => $entryDef->getName()));
    }

    /**
     * @return \Modera\ConfigBundle\Entity\ConfigurationEntry[]
     */
    public function install()
    {
        $installedEntries = array();

        foreach ($this->provider->getItems() as $entryDef) {
            /* @var ConfigurationEntryInterface $entryDef */

            $entry = $this->findEntry($entryDef);
            if (!$entry) {
                $installedEntries[] = $entryDef;
                $entry = ConfigurationEntry::createFromDefinition($entryDef);
            } else {
                $entry->applyDefinition($entryDef);
            }
            $this->em->persist($entry);
        }
        $this->em->flush();

        return $installedEntries;
    }
}

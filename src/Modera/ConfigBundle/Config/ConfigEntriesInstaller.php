<?php

namespace Modera\ConfigBundle\Config;

use Doctrine\ORM\EntityManagerInterface;
use Modera\ConfigBundle\Entity\ConfigurationEntry;
use Modera\ExpanderBundle\Ext\ExtensionProvider;

/**
 * Collects instances of {@class ConfigurationEntry} from the system and persists them to the database. If a
 * configuration entry already exists it won't be updated.
 *
 * @copyright 2014 Modera Foundation
 */
class ConfigEntriesInstaller
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ExtensionProvider $extensionProvider,
    ) {
    }

    private function findEntry(ConfigurationEntryDefinition $entryDef): ?ConfigurationEntry
    {
        return $this->em->getRepository(ConfigurationEntry::class)->findOneBy(['name' => $entryDef->getName()]);
    }

    /**
     * @return ConfigurationEntryDefinition[]
     */
    public function install(): array
    {
        $installedEntries = [];

        foreach ($this->extensionProvider->get('modera_config.config_entries')->getItems() as $entryDef) {
            /** @var ConfigurationEntryDefinition $entryDef */
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

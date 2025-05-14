<?php

namespace Modera\ConfigBundle\Manager;

use Modera\ConfigBundle\Config\ConfigurationEntryInterface;

/**
 * @copyright 2016 Modera Foundation
 */
interface ConfigurationEntriesManagerInterface
{
    public function findOneByName(string $name, ?object $owner = null): ?ConfigurationEntryInterface;

    /**
     * @throws \RuntimeException When requested configuration property with name $name is not found
     */
    public function findOneByNameOrDie(string $name, ?object $owner = null): ConfigurationEntryInterface;

    /**
     * @throws ConfigurationEntryAlreadyExistsException
     */
    public function save(ConfigurationEntryInterface $entry): void;
}

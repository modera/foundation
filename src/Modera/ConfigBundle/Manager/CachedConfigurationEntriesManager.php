<?php

namespace Modera\ConfigBundle\Manager;

use Modera\ConfigBundle\Entity\ConfigurationEntry;
use Gaufrette\Adapter\Cache;
use Gaufrette\Adapter;

/**
 * @author Mamedov Iavar <lukas.addon@modera.net>
 * @copyright 2019 Modera Foundation
 */
class CachedConfigurationEntriesManager
{
    /**
     * @var ConfigurationEntriesManagerInterface
     */
    private $configManager;

    /**
     * @var Cache
     */
    private $cache = array();

    /**
     * @param ConfigurationEntriesManagerInterface $configManager
     * @param string $adapter
     */
    public function __construct(\Modera\ConfigBundle\Config\ConfigurationEntriesManagerInterface $configManager, $adapterClass)
    {
        $this->configManager = $configManager;
        $this->cache = new Cache(new $adapterClass(), new $adapterClass());
    }

    /**
     * @param string $name
     * @param object $owner
     *
     * @return ConfigurationEntryInterface
     */
    public function findValueByNameOrDie($name, $owner = null)
    {
        if ($this->cache->exists($name)) {
            return $this->cache->read($name);
        }

        $config = $this->configManager->findOneByNameOrDie($name, $owner);
        $this->cache->write($name, $config->getValue());

        return $config->getValue();
    }

}

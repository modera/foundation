<?php

namespace Modera\ConfigBundle\Config;

use Modera\ConfigBundle\Entity\ConfigurationEntry;

/**
 * @copyright 2014 Modera Foundation
 */
class AsIsHandler implements HandlerInterface
{
    public function getReadableValue(ConfigurationEntry $entry): mixed
    {
        return $entry->getDenormalizedValue();
    }

    public function getValue(ConfigurationEntry $entry): mixed
    {
        return $entry->getDenormalizedValue();
    }

    public function convertToStorageValue(mixed $value, ConfigurationEntry $entry): mixed
    {
        return $value;
    }
}

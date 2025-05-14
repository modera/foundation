<?php

namespace Modera\ConfigBundle\Config;

use Modera\ConfigBundle\Entity\ConfigurationEntry;

/**
 * @copyright 2014 Modera Foundation
 */
class DictionaryHandler implements HandlerInterface
{
    public function getReadableValue(ConfigurationEntry $entry): mixed
    {
        $cfg = $entry->getServerHandlerConfig();

        if (\is_array($cfg['dictionary'] ?? null) && isset($cfg['dictionary'][$entry->getDenormalizedValue()])) {
            return $cfg['dictionary'][$entry->getDenormalizedValue()];
        }

        return false;
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

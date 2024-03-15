<?php

namespace Modera\ConfigBundle\Config;

use Modera\ConfigBundle\Entity\ConfigurationEntry;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class DictionaryHandler implements HandlerInterface
{
    public function getReadableValue(ConfigurationEntry $entry)
    {
        $cfg = $entry->getServerHandlerConfig();

        if (\is_array($cfg['dictionary'] ?? null) && isset($cfg['dictionary'][$entry->getDenormalizedValue()])) {
            return $cfg['dictionary'][$entry->getDenormalizedValue()];
        }

        return false;
    }

    public function getValue(ConfigurationEntry $entry)
    {
        return $entry->getDenormalizedValue();
    }

    public function convertToStorageValue($value, ConfigurationEntry $entry)
    {
        return $value;
    }
}

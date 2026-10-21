<?php

namespace Modera\ConfigBundle\Config;

use Modera\ConfigBundle\Entity\ConfigurationEntry;

/**
 * Exposes two configuration properties:
 * - true_text
 * - false_text.
 *
 * @copyright 2014 Modera Foundation
 */
class BooleanHandler implements HandlerInterface
{
    public function getReadableValue(ConfigurationEntry $entry): string
    {
        $cfg = $entry->getServerHandlerConfig();

        $trueValue = \is_string($cfg['true_text'] ?? null) ? $cfg['true_text'] : 'true';
        $falseValue = \is_string($cfg['false_text'] ?? null) ? $cfg['false_text'] : 'false';

        return 1 == $entry->getDenormalizedValue() ? $trueValue : $falseValue;
    }

    public function getValue(ConfigurationEntry $entry): bool
    {
        return 1 == $entry->getDenormalizedValue() ? true : false;
    }

    public function convertToStorageValue(mixed $value, ConfigurationEntry $entry): bool
    {
        return \in_array($value, [1, 'true']);
    }
}

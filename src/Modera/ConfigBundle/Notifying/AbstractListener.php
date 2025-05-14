<?php

namespace Modera\ConfigBundle\Notifying;

use Modera\ConfigBundle\Entity\ConfigurationEntry;

/**
 * By extending this class you prevent to overwrite your class, if new method added.
 *
 * @copyright 2021 Modera Foundation
 */
abstract class AbstractListener implements ListenerInterface
{
    public function onConfigurationEntryAdded(ConfigurationEntry $entry): void
    {
        // do nothing
    }

    public function onConfigurationEntryUpdated(ConfigurationEntry $entry): void
    {
        // do nothing
    }

    public function onConfigurationEntryRemoved(ConfigurationEntry $entry): void
    {
        // do nothing
    }
}

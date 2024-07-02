<?php

namespace Modera\ConfigBundle\Notifying;

use Modera\ConfigBundle\Entity\ConfigurationEntry;

/**
 * By implementing this interface you can perform additional operations.
 *
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2021 Modera Foundation
 */
interface ListenerInterface
{
    /**
     * When this method is invoked given $entry has already been persisted and its primary key is available.
     */
    public function onConfigurationEntryAdded(ConfigurationEntry $entry): void;

    public function onConfigurationEntryUpdated(ConfigurationEntry $entry): void;

    public function onConfigurationEntryRemoved(ConfigurationEntry $entry): void;
}

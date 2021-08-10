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
     *
     * @param ConfigurationEntry $entry
     */
    public function onConfigurationEntryAdded(ConfigurationEntry $entry);

    /**
     * @param ConfigurationEntry $entry
     */
    public function onConfigurationEntryUpdated(ConfigurationEntry $entry);

    /**
     * @param ConfigurationEntry $entry
     */
    public function onConfigurationEntryRemoved(ConfigurationEntry $entry);
}
<?php

namespace Modera\ConfigBundle\Notifying;

use Modera\ConfigBundle\Entity\ConfigurationEntry;

/**
 * By extending this class you prevent to overwrite your class, if new method added.
 *
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2021 Modera Foundation
 */
abstract class AbstractListener implements ListenerInterface
{
    /**
     * {@inheritdoc}
     */
    public function onConfigurationEntryAdded(ConfigurationEntry $entry)
    {
        // do nothing
    }

    /**
     * {@inheritdoc}
     */
    public function onConfigurationEntryUpdated(ConfigurationEntry $entry)
    {
        // do nothing
    }

    /**
     * {@inheritdoc}
     */
    public function onConfigurationEntryRemoved(ConfigurationEntry $entry)
    {
        // do nothing
    }
}
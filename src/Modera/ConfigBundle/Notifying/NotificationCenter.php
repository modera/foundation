<?php

namespace Modera\ConfigBundle\Notifying;

use Modera\ConfigBundle\Entity\ConfigurationEntry;
use Modera\ExpanderBundle\Ext\ContributorInterface;

/**
 * @private
 *
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2021 Modera Foundation
 */
class NotificationCenter
{
    private ContributorInterface $contributor;

    public function __construct(ContributorInterface $contributor)
    {
        $this->contributor = $contributor;
    }

    public function notifyConfigurationEntryAdded(ConfigurationEntry $entry): void
    {
        /** @var ListenerInterface $listener */
        foreach ($this->contributor->getItems() as $listener) {
            $listener->onConfigurationEntryAdded($entry);
        }
    }

    public function notifyConfigurationEntryUpdated(ConfigurationEntry $entry): void
    {
        /** @var ListenerInterface $listener */
        foreach ($this->contributor->getItems() as $listener) {
            $listener->onConfigurationEntryUpdated($entry);
        }
    }

    public function notifyConfigurationEntryRemoved(ConfigurationEntry $entry): void
    {
        /** @var ListenerInterface $listener */
        foreach ($this->contributor->getItems() as $listener) {
            $listener->onConfigurationEntryRemoved($entry);
        }
    }
}

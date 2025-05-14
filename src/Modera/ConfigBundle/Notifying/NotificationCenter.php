<?php

namespace Modera\ConfigBundle\Notifying;

use Modera\ConfigBundle\Entity\ConfigurationEntry;
use Modera\ExpanderBundle\Ext\ExtensionProvider;

/**
 * @private
 *
 * @copyright 2021 Modera Foundation
 */
class NotificationCenter
{
    public function __construct(
        private ExtensionProvider $extensionProvider,
    ) {
    }

    public function notifyConfigurationEntryAdded(ConfigurationEntry $entry): void
    {
        /** @var ListenerInterface $listener */
        foreach ($this->extensionProvider->get('modera_config.notification_center_listeners')->getItems() as $listener) {
            $listener->onConfigurationEntryAdded($entry);
        }
    }

    public function notifyConfigurationEntryUpdated(ConfigurationEntry $entry): void
    {
        /** @var ListenerInterface $listener */
        foreach ($this->extensionProvider->get('modera_config.notification_center_listeners')->getItems() as $listener) {
            $listener->onConfigurationEntryUpdated($entry);
        }
    }

    public function notifyConfigurationEntryRemoved(ConfigurationEntry $entry): void
    {
        /** @var ListenerInterface $listener */
        foreach ($this->extensionProvider->get('modera_config.notification_center_listeners')->getItems() as $listener) {
            $listener->onConfigurationEntryRemoved($entry);
        }
    }
}

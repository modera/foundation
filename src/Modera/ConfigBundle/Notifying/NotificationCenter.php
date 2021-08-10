<?php

namespace Modera\ConfigBundle\Notifying;

use Sli\ExpanderBundle\Ext\ContributorInterface;
use Modera\ConfigBundle\Entity\ConfigurationEntry;

/**
 * @private
 *
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2021 Modera Foundation
 */
class NotificationCenter
{
    /**
     * @var ContributorInterface
     */
    private $contributor;

    /**
     * @param ContributorInterface $contributor
     */
    public function __construct(ContributorInterface $contributor)
    {
         $this->contributor = $contributor;
    }

    /**
     * @param ConfigurationEntry $entry
     */
    public function notifyConfigurationEntryAdded(ConfigurationEntry $entry)
    {
        foreach ($this->contributor->getItems() as $listener) {
            /* @var ListenerInterface $listener */
            $listener->onConfigurationEntryAdded($entry);
        }
    }

    /**
     * @param ConfigurationEntry $entry
     */
    public function notifyConfigurationEntryUpdated(ConfigurationEntry $entry)
    {
        foreach ($this->contributor->getItems() as $listener) {
            /* @var ListenerInterface $listener */
            $listener->onConfigurationEntryUpdated($entry);
        }
    }

    /**
     * @param ConfigurationEntry $entry
     */
    public function notifyConfigurationEntryRemoved(ConfigurationEntry $entry)
    {
        foreach ($this->contributor->getItems() as $listener) {
            /* @var ListenerInterface $listener */
            $listener->onConfigurationEntryRemoved($entry);
        }
    }
}
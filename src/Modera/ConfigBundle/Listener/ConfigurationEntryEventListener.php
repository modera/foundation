<?php

namespace Modera\ConfigBundle\Listener;

use Doctrine\ORM\Events;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Modera\ConfigBundle\Entity\ConfigurationEntry;
use Modera\ConfigBundle\Notifying\NotificationCenter;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2021 Modera Foundation
 */
class ConfigurationEntryEventListener
{
    /**
     * @var NotificationCenter
     */
    private $notificationCenter;

    /**
     * @param NotificationCenter $notificationCenter
     */
    public function __construct(NotificationCenter $notificationCenter)
    {
        $this->notificationCenter = $notificationCenter;
    }

    public function postPersist(ConfigurationEntry $entity, LifecycleEventArgs $args)
    {
        $this->notificationCenter->notifyConfigurationEntryAdded($entity);
    }

    public function postUpdate(ConfigurationEntry $entity, LifecycleEventArgs $args)
    {
        $this->notificationCenter->notifyConfigurationEntryUpdated($entity);
    }

    public function postRemove(ConfigurationEntry $entity, LifecycleEventArgs $args)
    {
        $this->notificationCenter->notifyConfigurationEntryRemoved($entity);
    }
}

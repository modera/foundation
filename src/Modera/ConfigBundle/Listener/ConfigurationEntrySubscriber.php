<?php

namespace Modera\ConfigBundle\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Modera\ConfigBundle\Entity\ConfigurationEntry;
use Modera\ConfigBundle\Notifying\NotificationCenter;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2021 Modera Foundation
 */
class ConfigurationEntrySubscriber implements EventSubscriber
{
    private NotificationCenter $notificationCenter;

    public function __construct(NotificationCenter $notificationCenter)
    {
        $this->notificationCenter = $notificationCenter;
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist,
            Events::postUpdate,
            Events::postRemove,
        ];
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();
        if ($entity instanceof ConfigurationEntry) {
            $this->notificationCenter->notifyConfigurationEntryAdded($entity);
        }
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();
        if ($entity instanceof ConfigurationEntry) {
            // $changeSet = $args->getEntityManager()->getUnitOfWork()->getEntityChangeSet($entity);
            $this->notificationCenter->notifyConfigurationEntryUpdated($entity);
        }
    }

    public function postRemove(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();
        if ($entity instanceof ConfigurationEntry) {
            $this->notificationCenter->notifyConfigurationEntryRemoved($entity);
        }
    }
}

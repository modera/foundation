<?php

namespace Modera\ConfigBundle\Listener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Modera\ConfigBundle\Entity\ConfigurationEntry;
use Modera\ConfigBundle\Notifying\NotificationCenter;

/**
 * @copyright 2021 Modera Foundation
 */
class ConfigurationEntryEventListener
{
    public function __construct(
        private readonly NotificationCenter $notificationCenter,
    ) {
    }

    public function postPersist(ConfigurationEntry $entity, LifecycleEventArgs $args): void
    {
        $this->notificationCenter->notifyConfigurationEntryAdded($entity);
    }

    public function postUpdate(ConfigurationEntry $entity, LifecycleEventArgs $args): void
    {
        $om = $args->getObjectManager();
        if ($om instanceof EntityManagerInterface) {
            $changesSet = $om->getUnitOfWork()->getEntityChangeSet($entity);

            foreach ($changesSet as $field => $changes) {
                if ('updatedAt' === $field) {
                    continue;
                }

                $hasValuableChanges = $changes[0] != $changes[1];
                if ($hasValuableChanges) {
                    $this->notificationCenter->notifyConfigurationEntryUpdated($entity);

                    return;
                }
            }
        } else {
            $this->notificationCenter->notifyConfigurationEntryUpdated($entity);
        }
    }

    public function postRemove(ConfigurationEntry $entity, LifecycleEventArgs $args): void
    {
        $this->notificationCenter->notifyConfigurationEntryRemoved($entity);
    }
}

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
class ConfigurationEntrySubscriber implements EventSubscriber
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

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            Events::postPersist,
            Events::postUpdate,
            Events::postRemove,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof ConfigurationEntry) {
            $this->notificationCenter->notifyConfigurationEntryAdded($entity);
        }
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof ConfigurationEntry) {
            //$changeSet = $args->getEntityManager()->getUnitOfWork()->getEntityChangeSet($entity);
            $this->notificationCenter->notifyConfigurationEntryUpdated($entity);
        }
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof ConfigurationEntry) {
            $this->notificationCenter->notifyConfigurationEntryRemoved($entity);
        }
    }
}

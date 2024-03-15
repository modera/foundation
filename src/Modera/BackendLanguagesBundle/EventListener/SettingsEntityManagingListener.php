<?php

namespace Modera\BackendLanguagesBundle\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Modera\BackendLanguagesBundle\Entity\UserSettings;
use Modera\SecurityBundle\Entity\User;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class SettingsEntityManagingListener
{
    public function onFlush(OnFlushEventArgs $event): void
    {
        $em = $event->getObjectManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof User) {
                $userSettings = new UserSettings();
                $userSettings->setUser($entity);

                $em->persist($userSettings);
                $uow->computeChangeSet($em->getClassMetadata(UserSettings::class), $userSettings);
            }
        }

        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            if ($entity instanceof User) {
                $query = $em->createQuery(
                    \sprintf('DELETE FROM %s us WHERE us.user = ?0', UserSettings::class)
                );
                $query->execute([$entity]);
            }
        }
    }
}

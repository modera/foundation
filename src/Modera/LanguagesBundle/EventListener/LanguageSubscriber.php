<?php

namespace Modera\LanguagesBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Modera\LanguagesBundle\Entity\Language;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2020 Modera Foundation
 */
class LanguageSubscriber implements EventSubscriber
{
    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist,
            Events::postUpdate,
        ];
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $this->updateDefaultLanguage($args);
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $this->updateDefaultLanguage($args);
    }

    private function updateDefaultLanguage(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if ($entity instanceof Language) {
            if ($entity->isDefault()) {
                $em = $args->getEntityManager();
                $query = $em->createQuery(
                    \sprintf(
                        'UPDATE %s l SET l.isDefault = :status WHERE l.id != :id',
                        Language::class
                    )
                );
                $query->setParameter('status', false);
                $query->setParameter('id', $entity->getId());
                $query->execute();
            }
        }
    }
}

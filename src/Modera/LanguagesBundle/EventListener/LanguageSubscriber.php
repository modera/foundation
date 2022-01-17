<?php

namespace Modera\LanguagesBundle\EventListener;

use Doctrine\ORM\Events;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Modera\LanguagesBundle\Entity\Language;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2020 Modera Foundation
 */
class LanguageSubscriber implements EventSubscriber
{
    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Events::postPersist,
            Events::postUpdate,
        ];
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $this->updateDefaultLanguage($args);
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->updateDefaultLanguage($args);
    }

    /**
     * @param LifecycleEventArgs $args
     */
    private function updateDefaultLanguage(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Language) {
            if ($entity->isDefault()) {
                $em = $args->getEntityManager();
                $query = $em->createQuery(
                    sprintf(
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

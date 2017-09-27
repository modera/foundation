<?php

namespace Modera\TranslationsBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Modera\TranslationsBundle\Entity\LanguageTranslationToken;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class LanguageTranslationTokenListener
{
    /**
     * @var bool
     */
    private $isActive = true;

    /**
     * @param bool $status
     */
    public function setActive($status)
    {
        $this->isActive = $status;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $this->updateTranslationToken($args);
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->updateTranslationToken($args);
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function updateTranslationToken(LifecycleEventArgs $args)
    {
        if ($this->isActive) {
            $em     = $args->getEntityManager();
            $entity = $args->getEntity();

            if ($entity instanceof LanguageTranslationToken) {
                    $translationToken = $entity->getTranslationToken();
                    $translations = $translationToken->getTranslations();
                    $translations[$entity->getLanguage()->getId()] = $this->hydrateLanguageTranslationToken($entity);
                    $translationToken->setTranslations($translations);
                    $em->persist($translationToken);
                    $em->flush();
            }
        }
    }

    /**
     * @param LanguageTranslationToken $ltt
     * @return array
     */
    public function hydrateLanguageTranslationToken(LanguageTranslationToken $ltt)
    {
        return array(
            'id' => $ltt->getId(),
            'isNew' => $ltt->isNew(),
            'translation' => $ltt->getTranslation(),
            'locale' => $ltt->getLanguage()->getLocale(),
            'language' => $ltt->getLanguage()->getName(),
        );
    }
}

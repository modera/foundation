<?php

namespace Modera\BackendLanguagesBundle\Contributions;

use Doctrine\ORM\EntityManager;
use Sli\ExpanderBundle\Ext\ContributorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Modera\MjrIntegrationBundle\Config\ConfigMergerInterface;
use Modera\BackendLanguagesBundle\Entity\UserSettings;
use Modera\LanguagesBundle\Helper\LocaleHelper;
use Modera\LanguagesBundle\Entity\Language;
use Modera\SecurityBundle\Entity\User;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class ConfigMergersProvider implements ContributorInterface, ConfigMergerInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var string
     */
    private $locale;

    /**
     * @param EntityManager         $em
     * @param TokenStorageInterface $tokenStorage,
     * @param string                $locale,
     */
    public function __construct(EntityManager $em, TokenStorageInterface $tokenStorage, $locale = 'en')
    {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
        $this->locale = $locale;
    }

    /**
     * @param array $currentConfig
     *
     * @return array
     */
    public function merge(array $currentConfig)
    {
        $locale = null;
        $languages = array();

        $token = $this->tokenStorage->getToken();
        if ($token && $token->getUser() instanceof User) {
            /* @var UserSettings $settings */
            $settings = $this->em->getRepository(UserSettings::class)->findOneBy(array('user' => $token->getUser()->getId()));
            if ($settings && $settings->getLanguage() && $settings->getLanguage()->isEnabled()) {
                $locale = $settings->getLanguage()->getLocale();
            }

            if (!$locale) {
                /* @var Language $defaultLanguage */
                $defaultLanguage = $this->em->getRepository(Language::class)->findOneBy(array(
                    'isDefault' => true,
                ));
                if ($defaultLanguage) {
                    $locale = $defaultLanguage->getLocale();
                }
            }
        }

        /* @var Language[] $dbLanguages */
        $dbLanguages = $this->em->getRepository(Language::class)->findBy(array('isEnabled' => true));
        foreach ($dbLanguages as $dbLanguage) {
            $languages[] = array(
                'id' => $dbLanguage->getId(),
                'name' => $dbLanguage->getName($locale ?: $this->locale),
                'locale' => $dbLanguage->getLocale(),
                'direction' => LocaleHelper::getDirection($dbLanguage->getLocale()),
                'default' => $dbLanguage->isDefault(),
            );
        }

        return array_merge($currentConfig, array(
            'modera_backend_languages' => array(
                'languages' => $languages,
                'locale' => $locale ?: $this->locale,
                'direction' => LocaleHelper::getDirection($locale ?: $this->locale),
            ),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        return array($this);
    }
}

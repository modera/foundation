<?php

namespace Modera\BackendLanguagesBundle\Contributions;

use Doctrine\ORM\EntityManager;
use Sli\ExpanderBundle\Ext\ContributorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Modera\MjrIntegrationBundle\Config\ConfigMergerInterface;
use Modera\BackendLanguagesBundle\Entity\UserSettings;
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
        $languages = array();

        /* @var Language[] $dbLanguages */
        $dbLanguages = $this->em->getRepository(Language::clazz())->findBy(array('isEnabled' => true));
        foreach ($dbLanguages as $dbLanguage) {
            $languages[] = array(
                'id' => $dbLanguage->getId(),
                'name' => $dbLanguage->getName(),
                'locale' => $dbLanguage->getLocale(),
            );
        }

        $locale = $this->locale;
        $token = $this->tokenStorage->getToken();
        if ($token->isAuthenticated() && $token->getUser() instanceof User) {
            /* @var UserSettings $settings */
            $settings = $this->em->getRepository(UserSettings::clazz())->findOneBy(array('user' => $token->getUser()->getId()));
            if ($settings && $settings->getLanguage() && $settings->getLanguage()->getEnabled()) {
                $locale = $settings->getLanguage()->getLocale();
            }
        }

        return array_merge($currentConfig, array(
            'modera_backend_languages' => array(
                'languages' => $languages,
                'locale' => $locale,
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

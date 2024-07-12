<?php

namespace Modera\BackendLanguagesBundle\Contributions;

use Doctrine\ORM\EntityManagerInterface;
use Modera\BackendLanguagesBundle\Entity\UserSettings;
use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\LanguagesBundle\Entity\Language;
use Modera\LanguagesBundle\Helper\LocaleHelper;
use Modera\MjrIntegrationBundle\Config\ConfigMergerInterface;
use Modera\SecurityBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class ConfigMergersProvider implements ContributorInterface, ConfigMergerInterface
{
    private EntityManagerInterface $em;

    private TokenStorageInterface $tokenStorage;

    private string $locale;

    public function __construct(EntityManagerInterface $em, TokenStorageInterface $tokenStorage, string $locale = 'en')
    {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
        $this->locale = $locale;
    }

    public function merge(array $existingConfig): array
    {
        $locale = null;
        $languages = [];

        $token = $this->tokenStorage->getToken();
        if ($token && $token->getUser() instanceof User) {
            /** @var ?UserSettings $settings */
            $settings = $this->em->getRepository(UserSettings::class)->findOneBy(['user' => $token->getUser()->getId()]);
            if ($settings && $settings->getLanguage() && $settings->getLanguage()->isEnabled()) {
                $locale = $settings->getLanguage()->getLocale();
            }

            if (!$locale) {
                /** @var ?Language $defaultLanguage */
                $defaultLanguage = $this->em->getRepository(Language::class)->findOneBy([
                    'isDefault' => true,
                ]);
                if ($defaultLanguage) {
                    $locale = $defaultLanguage->getLocale();
                }
            }
        }

        /** @var Language[] $dbLanguages */
        $dbLanguages = $this->em->getRepository(Language::class)->findBy(['isEnabled' => true]);
        foreach ($dbLanguages as $dbLanguage) {
            $languages[] = [
                'id' => $dbLanguage->getId(),
                'name' => $dbLanguage->getName($locale ?: $this->locale),
                'locale' => $dbLanguage->getLocale(),
                'direction' => LocaleHelper::getDirection($dbLanguage->getLocale()),
                'default' => $dbLanguage->isDefault(),
            ];
        }

        return \array_merge($existingConfig, [
            'modera_backend_languages' => [
                'languages' => $languages,
                'locale' => $locale ?: $this->locale,
                'direction' => LocaleHelper::getDirection($locale ?: $this->locale),
            ],
        ]);
    }

    public function getItems(): array
    {
        return [$this];
    }
}

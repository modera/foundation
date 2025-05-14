<?php

namespace Modera\BackendLanguagesBundle\Contributions;

use Doctrine\ORM\EntityManagerInterface;
use Modera\ExpanderBundle\Ext\AsContributorFor;
use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\LanguagesBundle\Entity\Language;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @copyright 2020 Modera Foundation
 */
#[AsContributorFor('modera_mjr_integration.js_resources')]
class JsResourcesProvider implements ContributorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly string $defaultLocale = 'en',
    ) {
    }

    public function getItems(): array
    {
        $locale = $this->defaultLocale;

        /** @var ?Language $defaultLanguage */
        $defaultLanguage = $this->em->getRepository(Language::class)->findOneBy([
            'isDefault' => true,
        ]);
        if ($defaultLanguage) {
            $locale = $defaultLanguage->getLocale();
        }

        return [
            [
                'order' => PHP_INT_MIN + 10,
                'resource' => $this->urlGenerator->generate('modera_backend_languages_extjs_l10n', [
                    'locale' => $locale,
                ]),
            ],
        ];
    }
}

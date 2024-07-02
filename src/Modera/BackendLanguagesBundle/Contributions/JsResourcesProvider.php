<?php

namespace Modera\BackendLanguagesBundle\Contributions;

use Doctrine\ORM\EntityManagerInterface;
use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\LanguagesBundle\Entity\Language;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * @author    Sergei VIzel <sergei.vizel@modera.org>
 * @copyright 2020 Modera Foundation
 */
class JsResourcesProvider implements ContributorInterface
{
    private EntityManagerInterface $em;

    private Router $router;

    private string $defaultLocale;

    public function __construct(
        EntityManagerInterface $em,
        Router $router,
        string $defaultLocale = 'en'
    ) {
        $this->em = $em;
        $this->router = $router;
        $this->defaultLocale = $defaultLocale;
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
                'resource' => $this->router->generate('modera_backend_languages_extjs_l10n', [
                    'locale' => $locale,
                ]),
            ],
        ];
    }
}

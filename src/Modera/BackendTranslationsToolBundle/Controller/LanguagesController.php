<?php

namespace Modera\BackendTranslationsToolBundle\Controller;

use Modera\BackendTranslationsToolBundle\ModeraBackendTranslationsToolBundle;
use Modera\LanguagesBundle\Entity\Language;
use Modera\ServerCrudBundle\Controller\AbstractCrudController;
use Symfony\Component\HttpKernel\Attribute\AsController;

/**
 * @copyright 2014 Modera Foundation
 */
#[AsController]
class LanguagesController extends AbstractCrudController
{
    public function getConfig(): array
    {
        return [
            'entity' => Language::class,
            'security' => [
                'role' => ModeraBackendTranslationsToolBundle::ROLE_ACCESS_BACKEND_TOOLS_TRANSLATIONS_SECTION,
            ],
            'hydration' => [
                'groups' => [
                    'list' => ['id', 'name', 'locale', 'isEnabled'],
                ],
                'profiles' => [
                    'list',
                ],
            ],
        ];
    }
}

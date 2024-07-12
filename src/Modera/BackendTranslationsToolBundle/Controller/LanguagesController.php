<?php

namespace Modera\BackendTranslationsToolBundle\Controller;

use Modera\BackendTranslationsToolBundle\ModeraBackendTranslationsToolBundle;
use Modera\LanguagesBundle\Entity\Language;
use Modera\ServerCrudBundle\Controller\AbstractCrudController;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
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

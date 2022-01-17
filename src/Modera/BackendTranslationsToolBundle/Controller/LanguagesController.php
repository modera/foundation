<?php

namespace Modera\BackendTranslationsToolBundle\Controller;

use Modera\LanguagesBundle\Entity\Language;
use Modera\ServerCrudBundle\Controller\AbstractCrudController;
use Modera\BackendTranslationsToolBundle\ModeraBackendTranslationsToolBundle;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class LanguagesController extends AbstractCrudController
{
    /**
     * @return array
     */
    public function getConfig()
    {
        return array(
            'entity' => Language::class,
            'security' => array(
                'role' => ModeraBackendTranslationsToolBundle::ROLE_ACCESS_BACKEND_TOOLS_TRANSLATIONS_SECTION,
            ),
            'hydration' => array(
                'groups' => array(
                    'list' => ['id', 'name', 'locale', 'isEnabled'],
                ),
                'profiles' => array(
                    'list',
                ),
            ),
        );
    }
}

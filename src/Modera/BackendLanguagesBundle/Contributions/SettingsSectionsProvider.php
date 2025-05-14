<?php

namespace Modera\BackendLanguagesBundle\Contributions;

use Modera\BackendConfigUtilsBundle\ModeraBackendConfigUtilsBundle;
use Modera\BackendToolsSettingsBundle\Section\StandardSection;
use Modera\ExpanderBundle\Ext\AsContributorFor;
use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\FoundationBundle\Translation\T;
use Modera\MjrIntegrationBundle\Model\FontAwesome;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @copyright 2017 Modera Foundation
 */
#[AsContributorFor('modera_backend_tools_settings.contributions.sections')]
class SettingsSectionsProvider implements ContributorInterface
{
    public function __construct(
        private AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    public function getItems(): array
    {
        $role = ModeraBackendConfigUtilsBundle::ROLE_ACCESS_BACKEND_SYSTEM_SETTINGS;
        if ($this->authorizationChecker->isGranted($role)) {
            return [
                new StandardSection(
                    'localisation',
                    T::trans('Localisation'),
                    'Modera.backend.languages.runtime.SettingsActivity',
                    FontAwesome::resolve('language', 'fas')
                ),
            ];
        }

        return [];
    }
}

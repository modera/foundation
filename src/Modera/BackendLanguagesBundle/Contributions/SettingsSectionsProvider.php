<?php

namespace Modera\BackendLanguagesBundle\Contributions;

use Modera\BackendConfigUtilsBundle\ModeraBackendConfigUtilsBundle;
use Modera\BackendToolsSettingsBundle\Section\StandardSection;
use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\FoundationBundle\Translation\T;
use Modera\MjrIntegrationBundle\Model\FontAwesome;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2017 Modera Foundation
 */
class SettingsSectionsProvider implements ContributorInterface
{
    private AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
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

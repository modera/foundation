<?php

namespace Modera\BackendLanguagesBundle\Contributions;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Modera\BackendConfigUtilsBundle\ModeraBackendConfigUtilsBundle;
use Modera\BackendToolsSettingsBundle\Section\StandardSection;
use Modera\MjrIntegrationBundle\Model\FontAwesome;
use Sli\ExpanderBundle\Ext\ContributorInterface;
use Modera\FoundationBundle\Translation\T;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2017 Modera Foundation
 */
class SettingsSectionsProvider implements ContributorInterface
{
    private $authorizationChecker;

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        $role = ModeraBackendConfigUtilsBundle::ROLE_ACCESS_BACKEND_SYSTEM_SETTINGS;
        if ($this->authorizationChecker->isGranted($role)) {
            return array(
                new StandardSection(
                    'localisation',
                    T::trans('Localisation'),
                    'Modera.backend.languages.runtime.SettingsActivity',
                    FontAwesome::resolve('language', 'fas')
                ),
            );
        }

        return array();
    }
}

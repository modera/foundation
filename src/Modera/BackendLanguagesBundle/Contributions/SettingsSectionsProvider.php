<?php

namespace Modera\BackendLanguagesBundle\Contributions;

use Modera\FoundationBundle\Translation\T;
use Sli\ExpanderBundle\Ext\ContributorInterface;
use Modera\BackendToolsSettingsBundle\Section\StandardSection;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Modera\BackendConfigUtilsBundle\ModeraBackendConfigUtilsBundle;

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
        $roles = array(
            ModeraBackendConfigUtilsBundle::ROLE_ACCESS_BACKEND_SYSTEM_SETTINGS
        );
        if ($this->authorizationChecker->isGranted($roles)) {
            return array(
                new StandardSection(
                    'localisation',
                    T::trans('Localisation'),
                    'Modera.backend.languages.runtime.SettingsActivity',
                    'language'
                ),
            );
        }

        return array();
    }
}

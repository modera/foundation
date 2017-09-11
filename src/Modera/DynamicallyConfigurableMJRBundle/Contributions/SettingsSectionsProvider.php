<?php

namespace Modera\DynamicallyConfigurableMJRBundle\Contributions;

use Modera\BackendToolsSettingsBundle\Section\StandardSection;
use Sli\ExpanderBundle\Ext\ContributorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Modera\BackendConfigUtilsBundle\ModeraBackendConfigUtilsBundle;

/**
 * @internal Since 2.56.0
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class SettingsSectionsProvider implements ContributorInterface
{
    private $items;

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
        if (!$this->items) {
            $this->items = array();
            if ($this->authorizationChecker->isGranted(ModeraBackendConfigUtilsBundle::ROLE_ACCESS_BACKEND_SYSTEM_SETTINGS)) {
                $this->items[] = new StandardSection(
                    'general',
                    'General',
                    'Modera.backend.dcmjr.runtime.GeneralSiteSettingsActivity',
                    'gear',
                    array(
                        'activationParams' => array(
                            'category' => 'general',
                        ),
                    )
                );
            }
        }

        return $this->items;
    }

    /**
     * @return string
     */
    public static function clazz()
    {
        return get_called_class();
    }
}

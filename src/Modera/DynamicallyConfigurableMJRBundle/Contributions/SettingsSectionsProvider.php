<?php

namespace Modera\DynamicallyConfigurableMJRBundle\Contributions;

use Modera\BackendConfigUtilsBundle\ModeraBackendConfigUtilsBundle;
use Modera\BackendToolsSettingsBundle\Section\StandardSection;
use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\MjrIntegrationBundle\Model\FontAwesome;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @internal Since 2.56.0
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class SettingsSectionsProvider implements ContributorInterface
{
    /**
     * @var ?StandardSection[]
     */
    private ?array $items = null;

    private AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function getItems(): array
    {
        if (!$this->items) {
            $this->items = [];
            if ($this->authorizationChecker->isGranted(ModeraBackendConfigUtilsBundle::ROLE_ACCESS_BACKEND_SYSTEM_SETTINGS)) {
                $this->items[] = new StandardSection(
                    'general',
                    'General',
                    'Modera.backend.dcmjr.runtime.GeneralSiteSettingsActivity',
                    FontAwesome::resolve('cog', 'fas'),
                    [
                        'activationParams' => [
                            'category' => 'general',
                        ],
                    ]
                );
            }
        }

        return $this->items;
    }
}

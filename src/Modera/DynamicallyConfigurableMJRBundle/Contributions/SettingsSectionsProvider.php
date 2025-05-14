<?php

namespace Modera\DynamicallyConfigurableMJRBundle\Contributions;

use Modera\BackendConfigUtilsBundle\ModeraBackendConfigUtilsBundle;
use Modera\BackendToolsSettingsBundle\Section\StandardSection;
use Modera\ExpanderBundle\Ext\AsContributorFor;
use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\MjrIntegrationBundle\Model\FontAwesome;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @internal
 *
 * @copyright 2014 Modera Foundation
 */
#[AsContributorFor('modera_backend_tools_settings.contributions.sections')]
class SettingsSectionsProvider implements ContributorInterface
{
    /**
     * @var ?StandardSection[]
     */
    private ?array $items = null;

    public function __construct(
        private readonly AuthorizationCheckerInterface $authorizationChecker,
    ) {
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
                    ],
                );
            }
        }

        return $this->items;
    }
}

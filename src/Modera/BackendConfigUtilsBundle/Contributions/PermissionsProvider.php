<?php

namespace Modera\BackendConfigUtilsBundle\Contributions;

use Modera\BackendConfigUtilsBundle\ModeraBackendConfigUtilsBundle;
use Modera\ExpanderBundle\Ext\AsContributorFor;
use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\FoundationBundle\Translation\T;
use Modera\SecurityBundle\Model\Permission;

/**
 * @internal
 *
 * @copyright 2017 Modera Foundation
 */
#[AsContributorFor('modera_security.permissions')]
class PermissionsProvider implements ContributorInterface
{
    /**
     * @var Permission[]
     */
    private ?array $items = null;

    public function getItems(): array
    {
        if (!$this->items) {
            $this->items = [
                new Permission(
                    T::trans('Access System Settings'),
                    ModeraBackendConfigUtilsBundle::ROLE_ACCESS_BACKEND_SYSTEM_SETTINGS,
                    'administration',
                ),
            ];
        }

        return $this->items;
    }
}

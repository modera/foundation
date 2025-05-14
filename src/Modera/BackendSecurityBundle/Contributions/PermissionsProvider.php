<?php

namespace Modera\BackendSecurityBundle\Contributions;

use Modera\BackendSecurityBundle\ModeraBackendSecurityBundle;
use Modera\ExpanderBundle\Ext\AsContributorFor;
use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\FoundationBundle\Translation\T;
use Modera\SecurityBundle\Model\Permission;

/**
 * @copyright 2014 Modera Foundation
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
                    T::trans('Access Users Manager'),
                    ModeraBackendSecurityBundle::ROLE_ACCESS_BACKEND_TOOLS_SECURITY_SECTION,
                    'administration',
                ),
                new Permission(
                    T::trans('Add and remove User Accounts'),
                    ModeraBackendSecurityBundle::ROLE_MANAGE_USER_ACCOUNTS,
                    'administration',
                ),
                new Permission(
                    T::trans('Manage User Profiles'),
                    ModeraBackendSecurityBundle::ROLE_MANAGE_USER_PROFILES,
                    'administration',
                ),
                new Permission(
                    T::trans('Manage User Profile Information'),
                    ModeraBackendSecurityBundle::ROLE_MANAGE_USER_PROFILE_INFORMATION,
                    'administration',
                ),
                new Permission(
                    T::trans('Manage Groups and Permissions'),
                    ModeraBackendSecurityBundle::ROLE_MANAGE_PERMISSIONS,
                    'administration',
                ),
            ];
        }

        return $this->items;
    }
}

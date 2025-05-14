<?php

namespace Modera\MJRSecurityIntegrationBundle\Contributions;

use Modera\ExpanderBundle\Ext\AsContributorFor;
use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\FoundationBundle\Translation\T;
use Modera\MJRSecurityIntegrationBundle\ModeraMJRSecurityIntegrationBundle;
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
                    T::trans('Access Backend'),
                    ModeraMJRSecurityIntegrationBundle::ROLE_BACKEND_USER,
                    'general',
                ),
            ];
        }

        return $this->items;
    }
}

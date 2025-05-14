<?php

namespace Modera\MJRSecurityIntegrationBundle\Contributions;

use Modera\ExpanderBundle\Ext\AsContributorFor;
use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\FoundationBundle\Translation\T;
use Modera\SecurityBundle\Model\PermissionCategory;

/**
 * @copyright 2014 Modera Foundation
 */
#[AsContributorFor('modera_security.permission_categories')]
class PermissionCategoriesProvider implements ContributorInterface
{
    /**
     * @var PermissionCategory[]
     */
    private ?array $items = null;

    public function getItems(): array
    {
        if (!$this->items) {
            $this->items = [
                new PermissionCategory(
                    T::trans('General'),
                    'general',
                ),
            ];
        }

        return $this->items;
    }
}

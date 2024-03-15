<?php

namespace Modera\MJRSecurityIntegrationBundle\Contributions;

use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\FoundationBundle\Translation\T;
use Modera\SecurityBundle\Model\PermissionCategory;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
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
                // See notes related to MPFE-963 in CHANGELOG-2.x.md (release 2.55.0)
                new PermissionCategory(
                    T::trans('General'),
                    'general'
                ),
            ];
        }

        return $this->items;
    }
}

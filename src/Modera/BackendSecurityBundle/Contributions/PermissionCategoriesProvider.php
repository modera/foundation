<?php

namespace Modera\BackendSecurityBundle\Contributions;

use Modera\SecurityBundle\Model\PermissionCategory;
use Modera\FoundationBundle\Translation\T;
use Sli\ExpanderBundle\Ext\ContributorInterface;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class PermissionCategoriesProvider implements ContributorInterface
{
    private $items;

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        if (!$this->items) {
            $this->items = [
                new PermissionCategory(
                    T::trans('Administration'), // MPFE-959; before 2.54.0 it was "User management"
                    'user-management'
                ),
            ];
        }

        return $this->items;
    }
}

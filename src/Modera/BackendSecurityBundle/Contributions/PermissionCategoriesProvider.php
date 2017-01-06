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
                // We can afford contributing several permission group with identical labels because in backend UI
                // their are grouped by the label, not by 'technical name', meaning that even if contributers
                // contribute to different 'technical names', all contribution still will be presented correctly from
                // user perspective, under single "Administration" permission group
                new PermissionCategory(
                    T::trans('Administration'), // MPFE-959; before 2.54.0 it was "User management"
                    'user-management' // deprecated, use "administration" instead
                ),
                new PermissionCategory(
                    T::trans('Administration'), // MPFE-964, so that new code can already contribute to this
                    'administration' // this proper "permission category"
                ),
            ];
        }

        return $this->items;
    }
}

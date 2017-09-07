<?php

namespace Modera\BackendTranslationsToolBundle\Contributions;

use Modera\FoundationBundle\Translation\T;
use Modera\SecurityBundle\Model\PermissionCategory;
use Sli\ExpanderBundle\Ext\ContributorInterface;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2017 Modera Foundation
 */
class PermissionCategoriesProvider implements ContributorInterface
{
    /**
     * @var PermissionCategory[]
     */
    private $items;

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        if (!$this->items) {
            $this->items = [
                new PermissionCategory(
                    T::trans('Administration'),
                    'administration'
                ),
            ];
        }

        return $this->items;
    }
}

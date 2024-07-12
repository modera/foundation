<?php

namespace Modera\BackendTranslationsToolBundle\Contributions;

use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\FoundationBundle\Translation\T;
use Modera\SecurityBundle\Model\PermissionCategory;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2017 Modera Foundation
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
                new PermissionCategory(
                    T::trans('Administration'),
                    'administration'
                ),
            ];
        }

        return $this->items;
    }
}

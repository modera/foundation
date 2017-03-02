<?php

namespace Modera\MJRSecurityIntegrationBundle\Contributions;

use Modera\SecurityBundle\Model\PermissionCategory;
use Modera\FoundationBundle\Translation\T;
use Sli\ExpanderBundle\Ext\ContributorInterface;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
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

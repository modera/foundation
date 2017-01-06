<?php

namespace Modera\BackendToolsBundle\Contributions;

use Modera\BackendToolsBundle\ModeraBackendToolsBundle;
use Modera\SecurityBundle\Model\Permission;
use Modera\FoundationBundle\Translation\T;
use Sli\ExpanderBundle\Ext\ContributorInterface;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class PermissionsProvider implements ContributorInterface
{
    private $items;

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        if (!$this->items) {
            $this->items = [
                new Permission(
                    T::trans('Access Tools Section'), // MPFE-959; before 2.54.0 it was "Access Tools section"
                    ModeraBackendToolsBundle::ROLE_ACCESS_TOOLS_SECTION,
                    'administration'
                ),
            ];
        }

        return $this->items;
    }
}

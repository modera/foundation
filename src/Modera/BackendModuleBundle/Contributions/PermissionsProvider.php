<?php

namespace Modera\BackendModuleBundle\Contributions;

use Modera\BackendModuleBundle\ModeraBackendModuleBundle;
use Modera\SecurityBundle\Model\Permission;
use Modera\FoundationBundle\Translation\T;
use Sli\ExpanderBundle\Ext\ContributorInterface;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class PermissionsProvider implements ContributorInterface
{
    /**
     * @var Permission[]
     */
    private $items;

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        if (!$this->items) {
            $this->items = [
                new Permission(
                    T::trans('Access Modules Manager'), // MPFE-959; before 2.54.0 it was "Access module manager"
                    ModeraBackendModuleBundle::ROLE_ACCESS_BACKEND_TOOLS_MODULES_SECTION,
                    'administration'
                ),
            ];
        }

        return $this->items;
    }
}

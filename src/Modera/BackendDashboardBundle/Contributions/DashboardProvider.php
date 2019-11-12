<?php

namespace Modera\BackendDashboardBundle\Contributions;

use Modera\BackendDashboardBundle\Dashboard\DashboardInterface;
use Modera\BackendDashboardBundle\Dashboard\SimpleDashboard;
use Sli\ExpanderBundle\Ext\ContributorInterface;

/**
 * @deprecated Seems to be not used anymore anywhere ?
 *
 * Contributes js-runtime menu items
 *
 * @author    Alex Rudakov <alexandr.rudakov@modera.org>
 * @copyright 2014 Modera Foundation
 */
class DashboardProvider implements ContributorInterface
{
    /**
     * @var array
     */
    private $items;

    /**
     * @return DashboardInterface[]
     */
    public function getItems()
    {
        if (!$this->items) {
            $this->items = array(
                new SimpleDashboard('default', 'Default dashboard', 'Modera.backend.dashboard.runtime.SampleDashboardActivity'),
            );
        }

        return $this->items;
    }
}

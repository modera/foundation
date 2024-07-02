<?php

namespace Modera\BackendDashboardBundle\Contributions;

use Modera\ExpanderBundle\Ext\ContributorInterface;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class CssResourcesProvider implements ContributorInterface
{
    public function getItems(): array
    {
        return [
            '/bundles/moderabackenddashboard/css/styles.css',
        ];
    }
}

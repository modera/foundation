<?php

namespace Modera\BackendToolsBundle\Contributions;

use Modera\ExpanderBundle\Ext\ContributorInterface;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2013 Modera Foundation
 */
class CssResourcesProvider implements ContributorInterface
{
    public function getItems(): array
    {
        return [
            '/bundles/moderabackendtools/css/styles.css',
        ];
    }
}

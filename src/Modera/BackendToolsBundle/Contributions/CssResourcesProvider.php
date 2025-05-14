<?php

namespace Modera\BackendToolsBundle\Contributions;

use Modera\ExpanderBundle\Ext\AsContributorFor;
use Modera\ExpanderBundle\Ext\ContributorInterface;

/**
 * @copyright 2013 Modera Foundation
 */
#[AsContributorFor('modera_mjr_integration.css_resources')]
class CssResourcesProvider implements ContributorInterface
{
    public function getItems(): array
    {
        return [
            '/bundles/moderabackendtools/css/styles.css',
        ];
    }
}

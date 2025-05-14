<?php

namespace Modera\MjrIntegrationBundle\Contributions;

use Modera\ExpanderBundle\Ext\AsContributorFor;
use Modera\ExpanderBundle\Ext\ContributorInterface;

/**
 * @copyright 2015 Modera Foundation
 */
#[AsContributorFor('modera_mjr_integration.class_loader_mappings')]
class ClassLoaderMappingsProvider implements ContributorInterface
{
    public function getItems(): array
    {
        return [
            'Modera.mjrintegration' => '/bundles/moderamjrintegration/js',
        ];
    }
}

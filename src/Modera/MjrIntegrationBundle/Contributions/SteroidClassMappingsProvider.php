<?php

namespace Modera\MjrIntegrationBundle\Contributions;

use Modera\ExpanderBundle\Ext\AsContributorFor;
use Modera\ExpanderBundle\Ext\ContributorInterface;

/**
 * This contribution will only be used if ModeraBackendOnSteroidsBundle bundle is installed.
 *
 * @copyright 2015 Modera Foundation
 */
#[AsContributorFor('modera_backend_on_steroids.extjs_classes_paths')]
class SteroidClassMappingsProvider implements ContributorInterface
{
    public function getItems(): array
    {
        return [
            '@ModeraMjrIntegrationBundle/Resources/public/js/runtime',
        ];
    }
}

<?php

namespace Modera\FileUploaderBundle\Contributions;

use Modera\ExpanderBundle\Ext\AsContributorFor;
use Modera\ExpanderBundle\Ext\ContributorInterface;

/**
 * @copyright 2015 Modera Foundation
 */
#[AsContributorFor('modera_routing.routing_resources')]
class RoutingResourcesProvider implements ContributorInterface
{
    public function getItems(): array
    {
        return [
            '@ModeraFileUploaderBundle/Resources/config/routing.yaml',
        ];
    }
}

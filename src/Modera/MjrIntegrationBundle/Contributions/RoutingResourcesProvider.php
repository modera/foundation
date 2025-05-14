<?php

namespace Modera\MjrIntegrationBundle\Contributions;

use Modera\ExpanderBundle\Ext\AsContributorFor;
use Modera\ExpanderBundle\Ext\ContributorInterface;

/**
 * @copyright 2014 Modera Foundation
 */
#[AsContributorFor('modera_routing.routing_resources')]
class RoutingResourcesProvider implements ContributorInterface
{
    public function getItems(): array
    {
        return [
            '@ModeraMjrIntegrationBundle/Resources/config/routing.yaml',
        ];
    }
}

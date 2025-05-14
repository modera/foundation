<?php

namespace Modera\MJRSecurityIntegrationBundle\Contributions;

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
            '@ModeraMJRSecurityIntegrationBundle/Resources/config/routing.yaml',
        ];
    }
}

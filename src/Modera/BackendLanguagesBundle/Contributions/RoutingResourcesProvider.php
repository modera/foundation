<?php

namespace Modera\BackendLanguagesBundle\Contributions;

use Modera\ExpanderBundle\Ext\ContributorInterface;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class RoutingResourcesProvider implements ContributorInterface
{
    public function getItems(): array
    {
        return [
            '@ModeraBackendLanguagesBundle/Resources/config/routing.yml',
        ];
    }
}

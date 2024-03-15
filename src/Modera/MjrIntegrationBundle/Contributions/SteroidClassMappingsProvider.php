<?php

namespace Modera\MjrIntegrationBundle\Contributions;

use Modera\ExpanderBundle\Ext\ContributorInterface;

/**
 * This contribution will only be used if ModeraBackendOnSteroidsBundle bundle is installed.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2015 Modera Foundation
 */
class SteroidClassMappingsProvider implements ContributorInterface
{
    public function getItems(): array
    {
        return [
            '@ModeraMjrIntegrationBundle/Resources/public/js/runtime',
        ];
    }
}

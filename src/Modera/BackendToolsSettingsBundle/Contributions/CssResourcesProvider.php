<?php

namespace Modera\BackendToolsSettingsBundle\Contributions;

use Modera\ExpanderBundle\Ext\ContributorInterface;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class CssResourcesProvider implements ContributorInterface
{
    public function getItems(): array
    {
        return [
            '/bundles/moderabackendtoolssettings/css/styles.css',
        ];
    }
}

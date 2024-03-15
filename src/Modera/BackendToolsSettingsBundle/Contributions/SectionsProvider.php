<?php

namespace Modera\BackendToolsSettingsBundle\Contributions;

use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\MjrIntegrationBundle\Sections\Section;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class SectionsProvider implements ContributorInterface
{
    /**
     * @var Section[]
     */
    private ?array $items = null;

    public function getItems(): array
    {
        if (!$this->items) {
            $this->items = [
                new Section('tools.settings', 'Modera.backend.tools.settings.runtime.Section', [
                    Section::META_NAMESPACE => 'Modera.backend.tools.settings',
                    Section::META_NAMESPACE_PATH => '/bundles/moderabackendtoolssettings/js',
                ]),
            ];
        }

        return $this->items;
    }
}

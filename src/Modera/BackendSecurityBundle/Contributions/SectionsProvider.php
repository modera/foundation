<?php

namespace Modera\BackendSecurityBundle\Contributions;

use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\MjrIntegrationBundle\Sections\Section;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
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
                new Section('tools.security', 'Modera.backend.security.toolscontribution.runtime.Section', [
                    Section::META_NAMESPACE => 'Modera.backend.security',
                    Section::META_NAMESPACE_PATH => '/bundles/moderabackendsecurity/js',
                ]),
            ];
        }

        return $this->items;
    }
}

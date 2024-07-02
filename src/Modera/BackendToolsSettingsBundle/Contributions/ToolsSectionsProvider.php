<?php

namespace Modera\BackendToolsSettingsBundle\Contributions;

use Modera\BackendToolsBundle\Section\Section;
use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\FoundationBundle\Translation\T;

/**
 * Contributes a section to Backend/Tools.
 *
 * @internal Since 2.56.0
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class ToolsSectionsProvider implements ContributorInterface
{
    /**
     * @var Section[]
     */
    private ?array $items = null;

    private ContributorInterface $sectionsProvider;

    public function __construct(ContributorInterface $sectionsProvider)
    {
        $this->sectionsProvider = $sectionsProvider;
    }

    public function getItems(): array
    {
        if (!$this->items) {
            $this->items = [];
            if (\count($this->sectionsProvider->getItems())) {
                $this->items[] = new Section(
                    T::trans('Settings'),
                    'tools.settings',
                    T::trans('Configure the current site.'),
                    '',
                    '',
                    'modera-backend-tools-settings-icon'
                );
            }
        }

        return $this->items;
    }
}

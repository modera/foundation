<?php

namespace Modera\BackendToolsSettingsBundle\Contributions;

use Modera\BackendToolsBundle\Section\Section;
use Modera\ExpanderBundle\Ext\AsContributorFor;
use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\ExpanderBundle\Ext\ExtensionProvider;
use Modera\FoundationBundle\Translation\T;

/**
 * Contributes a section to Backend/Tools.
 *
 * @internal
 *
 * @copyright 2014 Modera Foundation
 */
#[AsContributorFor('modera_backend_tools.sections')]
class ToolsSectionsProvider implements ContributorInterface
{
    /**
     * @var Section[]
     */
    private ?array $items = null;

    public function __construct(
        private readonly ExtensionProvider $extensionProvider,
    ) {
    }

    public function getItems(): array
    {
        if (!$this->items) {
            $this->items = [];
            if (\count($this->extensionProvider->get('modera_backend_tools_settings.contributions.sections')->getItems())) {
                $this->items[] = new Section(
                    T::trans('Settings'),
                    'tools.settings',
                    T::trans('Configure the current site.'),
                    '',
                    '',
                    'modera-backend-tools-settings-icon',
                );
            }
        }

        return $this->items;
    }
}

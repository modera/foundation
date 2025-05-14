<?php

namespace Modera\BackendToolsSettingsBundle\Contributions;

use Modera\BackendToolsSettingsBundle\Section\SectionInterface;
use Modera\ExpanderBundle\Ext\ExtensionProvider;
use Modera\MjrIntegrationBundle\Config\ConfigMergerInterface;

/**
 * Merges settings sections to MJR runtime-config.
 *
 * @copyright 2014 Modera Foundation
 */
class SectionsConfigMerger implements ConfigMergerInterface
{
    public function __construct(
        private readonly ExtensionProvider $extensionProvider,
    ) {
    }

    public function merge(array $existingConfig): array
    {
        $existingConfig['settingsSections'] = [];

        /** @var SectionInterface $section */
        foreach ($this->extensionProvider->get('modera_backend_tools_settings.contributions.sections')->getItems() as $section) {
            $existingConfig['settingsSections'][] = [
                'id' => $section->getId(),
                'name' => $section->getName(),
                'activityClass' => $section->getActivityClass(),
                'glyph' => $section->getGlyph(),
                'meta' => $section->getMeta(),
            ];
        }

        return $existingConfig;
    }
}

<?php

namespace Modera\BackendToolsSettingsBundle\Contributions;

use Modera\BackendToolsSettingsBundle\Section\GroupSection;
use Modera\BackendToolsSettingsBundle\Section\NestedSectionInterface;
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
        $sections = [];
        /** @var SectionInterface $section */
        foreach ($this->extensionProvider->get('modera_backend_tools_settings.contributions.sections')->getItems() as $section) {
            $sections[$section->getId()] = [
                'id' => $section->getId(),
                'name' => $section->getName(),
                'activityClass' => $section->getActivityClass(),
                'glyph' => $section->getGlyph(),
                'meta' => $section->getMeta(),
                'parent' => $section instanceof NestedSectionInterface ? $section->getParent() : null,
                'group' => $section instanceof GroupSection,
            ];
        }

        foreach ($sections as $id => $section) {
            if (null !== $section['parent'] && !isset($sections[$section['parent']])) {
                $sections[$id]['parent'] = null;
            }
        }

        $declared = $sections;
        $children = [];
        foreach ($declared as $id => $section) {
            if (null === $section['parent']) {
                continue;
            }
            if (null === $declared[$section['parent']]['parent']) {
                $children[$section['parent']][] = $id;
            } else {
                $sections[$id]['parent'] = null;
            }
        }

        $existingConfig['settingsSections'] = [];
        foreach ($sections as $id => $section) {
            if (null !== $section['parent']) {
                continue;
            }
            $existingConfig['settingsSections'][] = $section;
            foreach ($children[$id] ?? [] as $childId) {
                $existingConfig['settingsSections'][] = $sections[$childId];
            }
        }

        return $existingConfig;
    }
}

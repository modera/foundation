<?php

namespace Modera\BackendToolsSettingsBundle\Contributions;

use Modera\BackendToolsSettingsBundle\Section\SectionInterface;
use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\MjrIntegrationBundle\Config\ConfigMergerInterface;

/**
 * Merges settings sections to MJR runtime-config.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class SectionsConfigMerger implements ConfigMergerInterface
{
    private ContributorInterface $sectionsProvider;

    public function __construct(ContributorInterface $sectionsProvider)
    {
        $this->sectionsProvider = $sectionsProvider;
    }

    public function merge(array $existingConfig): array
    {
        $existingConfig['settingsSections'] = [];

        /** @var SectionInterface $section */
        foreach ($this->sectionsProvider->getItems() as $section) {
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

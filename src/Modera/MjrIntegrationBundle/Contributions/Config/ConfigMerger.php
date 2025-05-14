<?php

namespace Modera\MjrIntegrationBundle\Contributions\Config;

use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\MjrIntegrationBundle\Config\ConfigMergerInterface;
use Modera\MjrIntegrationBundle\Config\MainConfigInterface;
use Modera\MjrIntegrationBundle\Menu\MenuManager;
use Modera\MjrIntegrationBundle\Sections\Section;

/**
 * Merges standard and very basic configuration.
 *
 * @copyright 2013 Modera Foundation
 */
class ConfigMerger implements ConfigMergerInterface
{
    public function __construct(
        private readonly MainConfigInterface $mainConfig,
        private readonly MenuManager $menuMgr,
        private readonly ContributorInterface $sectionsProvider,
        private readonly ContributorInterface $classLoaderMappingsProvider,
    ) {
    }

    public function merge(array $existingConfig): array
    {
        $menuItems = [];
        foreach ($this->menuMgr->getAll() as $menuItem) {
            $menuItems[] = [
                'id' => $menuItem->getId(),
                'glyph' => $menuItem->getGlyph(),
                'label' => $menuItem->getLabel(),
                'controller' => $menuItem->getController(),
                'metadata' => $menuItem->getMetadata(),
            ];
        }

        $sections = [];
        /** @var Section $section */
        foreach ($this->sectionsProvider->getItems() as $section) {
            $sections[] = [
                'id' => $section->getId(),
                'controller' => $section->getController(),
                'metadata' => $section->getMetadata(),
            ];
        }

        return \array_merge($existingConfig, [
            'deploymentName' => $this->mainConfig->getTitle(),
            'deploymentUrl' => $this->mainConfig->getUrl(),
            'homeSection' => $this->mainConfig->getHomeSection(),
            'sections' => $sections, // backend sections
            'menuItems' => $menuItems,
            'classLoaderMappings' => $this->classLoaderMappingsProvider->getItems(),
        ]);
    }
}

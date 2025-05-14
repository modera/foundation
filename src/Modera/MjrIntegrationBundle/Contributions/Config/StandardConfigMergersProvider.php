<?php

namespace Modera\MjrIntegrationBundle\Contributions\Config;

use Modera\ExpanderBundle\Ext\AsContributorFor;
use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\ExpanderBundle\Ext\ExtensionProvider;
use Modera\MjrIntegrationBundle\Config\MainConfigInterface;
use Modera\MjrIntegrationBundle\Menu\MenuManager;

/**
 * Provides standard configurators.
 *
 * @copyright 2013 Modera Foundation
 */
#[AsContributorFor('modera_mjr_integration.config.config_mergers')]
class StandardConfigMergersProvider implements ContributorInterface
{
    /**
     * @var ?ConfigMerger[]
     */
    private ?array $items = null;

    public function __construct(
        private readonly ExtensionProvider $extensionProvider,
        private readonly MainConfigInterface $mainConfig,
        private readonly MenuManager $menuManager,
    ) {
    }

    public function getItems(): array
    {
        if (!$this->items) {
            $this->items = [
                new ConfigMerger(
                    $this->mainConfig,
                    $this->menuManager,
                    $this->extensionProvider->get('modera_mjr_integration.sections'),
                    $this->extensionProvider->get('modera_mjr_integration.class_loader_mappings'),
                ),
            ];
        }

        return $this->items;
    }
}

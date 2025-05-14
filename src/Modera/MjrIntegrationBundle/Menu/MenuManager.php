<?php

namespace Modera\MjrIntegrationBundle\Menu;

use Modera\ExpanderBundle\Ext\ExtensionProvider;

/**
 * Manages menu.
 *
 * @copyright 2013 Modera Foundation
 */
class MenuManager
{
    public function __construct(
        private readonly ExtensionProvider $extensionProvider,
    ) {
    }

    /**
     * @return MenuItemInterface[]
     */
    public function getAll(): array
    {
        /** @var MenuItemInterface[] $items */
        $items = $this->extensionProvider->get('modera_mjr_integration.menu.menu_items')->getItems();

        return $items;
    }
}

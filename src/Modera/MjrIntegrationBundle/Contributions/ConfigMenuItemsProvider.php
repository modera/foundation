<?php

namespace Modera\MjrIntegrationBundle\Contributions;

use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\MjrIntegrationBundle\Menu\MenuItem;
use Modera\MjrIntegrationBundle\Menu\MenuItemInterface;

/**
 * Contributes js-runtime menu items based on a config defined in "modera_mjr_integration" namespace.
 *
 * @see \Modera\MjrIntegrationBundle\DependencyInjection\Configuration
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class ConfigMenuItemsProvider implements ContributorInterface
{
    /**
     * @var MenuItem[]
     */
    private array $items;

    /**
     * @var array{
     *     'menu_items'?: array{
     *         'id': string,
     *         'name': string,
     *         'namespace': string,
     *         'controller': string,
     *         'path': string
     *     }[]
     * }
     */
    private array $config;

    /**
     * @param array{
     *     'menu_items'?: array{
     *         'id': string,
     *         'name': string,
     *         'namespace': string,
     *         'controller': string,
     *         'path': string
     *     }[]
     * } $config
     */
    public function __construct(array $config)
    {
        if (!\is_array($config['menu_items'] ?? null)) {
            throw new \InvalidArgumentException('Given "$config" doesn\'t have key "menu_items" or it is not array!.');
        }
        $this->config = $config;
    }

    public function getItems(): array
    {
        if (!$this->items) {
            $this->items = [];
            foreach ($this->config['menu_items'] ?? [] as $menuItem) {
                $controller = \str_replace('$ns', $menuItem['namespace'], $menuItem['controller']);

                $this->items[] = new MenuItem($menuItem['name'], $controller, $menuItem['id'], [
                    MenuItemInterface::META_NAMESPACE => $menuItem['namespace'],
                    MenuItemInterface::META_NAMESPACE_PATH => $menuItem['path'],
                ]);
            }
        }

        return $this->items;
    }
}

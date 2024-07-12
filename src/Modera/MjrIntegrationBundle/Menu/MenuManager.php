<?php

namespace Modera\MjrIntegrationBundle\Menu;

use Modera\ExpanderBundle\Ext\ContributorInterface;

/**
 * Manages menu.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2013 Modera Foundation
 */
class MenuManager
{
    private ContributorInterface $provider;

    public function __construct(ContributorInterface $provider)
    {
        $this->provider = $provider;
    }

    /**
     * @return MenuItemInterface[]
     */
    public function getAll(): array
    {
        /** @var MenuItemInterface[] $items */
        $items = $this->provider->getItems();

        return $items;
    }
}

<?php

namespace Modera\BackendDashboardBundle\Contributions;

use Modera\ExpanderBundle\Ext\OrderedContributorInterface;
use Modera\FoundationBundle\Translation\T;
use Modera\MjrIntegrationBundle\Menu\MenuItem;
use Modera\MjrIntegrationBundle\Menu\MenuItemInterface;
use Modera\MjrIntegrationBundle\Model\FontAwesome;

/**
 * Contributes js-runtime menu items.
 *
 * @author    Alex Rudakov <alexandr.rudakov@modera.org>
 * @copyright 2014 Modera Foundation
 */
class MenuItemsProvider implements OrderedContributorInterface
{
    private ConfigMergersProvider $configMergersProvider;

    private int $tabOrder;

    /**
     * @var MenuItem[]
     */
    private ?array $items = null;

    /**
     * Registers dashboard item as a section tab in Backend.
     */
    public function __construct(ConfigMergersProvider $configMergersProvider, int $tabOrder = 0)
    {
        $this->configMergersProvider = $configMergersProvider;
        $this->tabOrder = $tabOrder;
    }

    public function getItems(): array
    {
        if (!$this->items) {
            $this->items = [];

            // Hide tab, if landing section not dashboard
            $landingSection = $this->configMergersProvider->getUserLandingSection();
            if ('dashboard' === $landingSection) {
                $this->items[] = new MenuItem(
                    T::trans('Dashboard'),
                    'Modera.backend.dashboard.runtime.Section',
                    'dashboard',
                    [
                        MenuItemInterface::META_NAMESPACE => 'Modera.backend.dashboard',
                        MenuItemInterface::META_NAMESPACE_PATH => '/bundles/moderabackenddashboard/js',
                    ],
                    FontAwesome::resolve('tachometer-alt', 'fas')
                );
            }
        }

        return $this->items;
    }

    /**
     * Return tab order.
     */
    public function getOrder(): int
    {
        return $this->tabOrder;
    }
}

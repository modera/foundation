<?php

namespace Modera\BackendDashboardBundle\Contributions;

use Modera\MjrIntegrationBundle\Menu\MenuItem;
use Modera\MjrIntegrationBundle\Menu\MenuItemInterface;
use Modera\MjrIntegrationBundle\Model\FontAwesome;
use Sli\ExpanderBundle\Ext\OrderedContributorInterface;
use Modera\BackendDashboardBundle\Contributions\ConfigMergersProvider;
use Modera\FoundationBundle\Translation\T;

/**
 * Contributes js-runtime menu items.
 *
 * @author    Alex Rudakov <alexandr.rudakov@modera.org>
 * @copyright 2014 Modera Foundation
 */
class MenuItemsProvider implements OrderedContributorInterface
{
    /**
     * @var ConfigMergersProvider
     */
    private $configMergersProvider;

    /**
     * @var array
     */
    private $items = array();

    /**
     * @var int
     */
    private $tabOrder;

    /**
     * Registers dashboard item as a section tab in Backend.
     *
     * @param ConfigMergersProvider $configMergersProvider
     * @param int $tabOrder
     */
    public function __construct(ConfigMergersProvider $configMergersProvider, $tabOrder = 0)
    {
        $this->configMergersProvider = $configMergersProvider;
        $this->tabOrder = $tabOrder;
    }

    /**
     * {@inheritdoc}
     *
     * @return MenuItemsProvider[]
     */
    public function getItems()
    {
        if (!$this->items) {
            $landingSection = $this->configMergersProvider->getUserLandingSection();

            // Hide tab, if landing section not dashboard
            if ('dashboard' === $landingSection) {
                $this->items = array(
                    new MenuItem(T::trans('Dashboard'), 'Modera.backend.dashboard.runtime.Section', 'dashboard', [
                        MenuItemInterface::META_NAMESPACE => 'Modera.backend.dashboard',
                        MenuItemInterface::META_NAMESPACE_PATH => '/bundles/moderabackenddashboard/js',
                    ], FontAwesome::resolve('tachometer-alt', 'fas')),
                );
            }
        }

        return $this->items;
    }

    /**
     * Return tab order.
     *
     * @return int
     */
    public function getOrder()
    {
        return $this->tabOrder;
    }
}

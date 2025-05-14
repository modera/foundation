<?php

namespace Modera\BackendToolsBundle\Contributions;

use Modera\BackendToolsBundle\ModeraBackendToolsBundle;
use Modera\ExpanderBundle\Ext\AsContributorFor;
use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\ExpanderBundle\Ext\ExtensionProvider;
use Modera\FoundationBundle\Translation\T;
use Modera\MjrIntegrationBundle\Menu\MenuItem;
use Modera\MjrIntegrationBundle\Menu\MenuItemInterface;
use Modera\MjrIntegrationBundle\Model\FontAwesome;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Contributes js-runtime menu items.
 *
 * @copyright 2013 Modera Foundation
 */
#[AsContributorFor('modera_mjr_integration.menu.menu_items')]
class MenuItemsProvider implements ContributorInterface
{
    /**
     * @var MenuItem[]
     */
    private ?array $items = null;

    public function __construct(
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly ExtensionProvider $extensionProvider,
        private readonly int $tabOrder,
    ) {
    }

    public function getItems(): array
    {
        if (!$this->items) {
            $this->items = [];

            if ($this->authorizationChecker->isGranted(ModeraBackendToolsBundle::ROLE_ACCESS_TOOLS_SECTION)) {
                if (\count($this->extensionProvider->get('modera_backend_tools.sections')->getItems())) {
                    $this->items[] = new MenuItem(
                        T::trans('Tools'),
                        'Modera.backend.tools.runtime.Section',
                        'tools',
                        [
                            MenuItemInterface::META_NAMESPACE => 'Modera.backend.tools',
                            MenuItemInterface::META_NAMESPACE_PATH => '/bundles/moderabackendtools/js',
                        ],
                        FontAwesome::resolve('wrench', 'fas'),
                    );
                }
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

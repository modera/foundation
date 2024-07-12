<?php

namespace Modera\BackendToolsBundle\Contributions;

use Modera\BackendToolsBundle\ModeraBackendToolsBundle;
use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\FoundationBundle\Translation\T;
use Modera\MjrIntegrationBundle\Menu\MenuItem;
use Modera\MjrIntegrationBundle\Menu\MenuItemInterface;
use Modera\MjrIntegrationBundle\Model\FontAwesome;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Contributes js-runtime menu items.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2013 Modera Foundation
 */
class MenuItemsProvider implements ContributorInterface
{
    private AuthorizationCheckerInterface $authorizationChecker;

    private ContributorInterface $sectionsProvider;

    private int $tabOrder;

    /**
     * @var MenuItem[]
     */
    private ?array $items = null;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        ContributorInterface $sectionsProvider,
        int $tabOrder
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->sectionsProvider = $sectionsProvider;
        $this->tabOrder = $tabOrder;
    }

    public function getItems(): array
    {
        if (!$this->items) {
            $this->items = [];

            if ($this->authorizationChecker->isGranted(ModeraBackendToolsBundle::ROLE_ACCESS_TOOLS_SECTION)) {
                if (\count($this->sectionsProvider->getItems())) {
                    $this->items[] = new MenuItem(
                        T::trans('Tools'),
                        'Modera.backend.tools.runtime.Section',
                        'tools',
                        [
                            MenuItemInterface::META_NAMESPACE => 'Modera.backend.tools',
                            MenuItemInterface::META_NAMESPACE_PATH => '/bundles/moderabackendtools/js',
                        ],
                        FontAwesome::resolve('wrench', 'fas')
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

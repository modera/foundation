<?php

namespace Modera\ServerCrudBundle\Contributions;

use Modera\ExpanderBundle\Ext\AsContributorFor;
use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\ServerCrudBundle\Intercepting\ControllerActionsInterceptorInterface;
use Modera\ServerCrudBundle\Security\SecurityControllerActionsInterceptor;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @copyright 2014 Modera Foundation
 */
#[AsContributorFor('modera_server_crud.intercepting.cai')]
class ControllerActionInterceptorsProvider implements ContributorInterface
{
    /**
     * @var ?ControllerActionsInterceptorInterface[]
     */
    private ?array $items = null;

    public function __construct(
        private readonly AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    public function getItems(): array
    {
        if (!$this->items) {
            $this->items = [
                new SecurityControllerActionsInterceptor($this->authorizationChecker),
            ];
        }

        return $this->items;
    }
}

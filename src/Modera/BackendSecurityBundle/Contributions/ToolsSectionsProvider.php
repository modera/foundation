<?php

namespace Modera\BackendSecurityBundle\Contributions;

use Modera\BackendSecurityBundle\ModeraBackendSecurityBundle;
use Modera\BackendToolsBundle\Section\Section;
use Modera\ExpanderBundle\Ext\AsContributorFor;
use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\FoundationBundle\Translation\T;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Contributes a section to Backend/Tools.
 *
 * @copyright 2014 Modera Foundation
 */
#[AsContributorFor('modera_backend_tools.sections')]
class ToolsSectionsProvider implements ContributorInterface
{
    /**
     * @var Section[]
     */
    private ?array $items = null;

    public function __construct(
        private readonly AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    public function getItems(): array
    {
        if (!$this->items) {
            $this->items = [];

            if ($this->authorizationChecker->isGranted(ModeraBackendSecurityBundle::ROLE_ACCESS_BACKEND_TOOLS_SECURITY_SECTION)) {
                $this->items[] = new Section(
                    T::trans('Security permissions'),
                    'tools.security',
                    T::trans('Control permissions of users/groups.'),
                    '',
                    '',
                    'modera-backend-security-tools-icon',
                );
            }
        }

        return $this->items;
    }
}

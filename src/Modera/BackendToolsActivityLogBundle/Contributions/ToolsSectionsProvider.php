<?php

namespace Modera\BackendToolsActivityLogBundle\Contributions;

use Modera\BackendToolsActivityLogBundle\ModeraBackendToolsActivityLogBundle;
use Modera\BackendToolsBundle\Section\Section;
use Modera\ExpanderBundle\Ext\AsContributorFor;
use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\FoundationBundle\Translation\T;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Contributes a section to Backend/Tools.
 *
 * @internal
 *
 * @copyright 2017 Modera Foundation
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

            if ($this->authorizationChecker->isGranted(ModeraBackendToolsActivityLogBundle::ROLE_ACCESS_BACKEND_TOOLS_ACTIVITY_LOG_SECTION)) {
                $this->items[] = new Section(
                    T::trans('Activity log'),
                    'tools.activitylog',
                    T::trans('See what activities recently have happened on the site'),
                    '',
                    '',
                    'modera-backend-tools-activity-log-icon',
                );
            }
        }

        return $this->items;
    }
}

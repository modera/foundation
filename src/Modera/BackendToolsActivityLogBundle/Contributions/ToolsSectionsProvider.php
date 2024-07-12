<?php

namespace Modera\BackendToolsActivityLogBundle\Contributions;

use Modera\BackendToolsActivityLogBundle\ModeraBackendToolsActivityLogBundle;
use Modera\BackendToolsBundle\Section\Section;
use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\FoundationBundle\Translation\T;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Contributes a section to Backend/Tools.
 *
 * @internal Since 2.56.0
 *
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2017 Modera Foundation
 */
class ToolsSectionsProvider implements ContributorInterface
{
    private AuthorizationCheckerInterface $authorizationChecker;

    /**
     * @var Section[]
     */
    private ?array $items = null;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
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
                    'modera-backend-tools-activity-log-icon'
                );
            }
        }

        return $this->items;
    }
}

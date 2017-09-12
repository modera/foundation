<?php

namespace Modera\BackendToolsActivityLogBundle\Contributions;

use Modera\FoundationBundle\Translation\T;
use Modera\BackendToolsBundle\Section\Section;
use Sli\ExpanderBundle\Ext\ContributorInterface;
use Modera\BackendToolsActivityLogBundle\ModeraBackendToolsActivityLogBundle;
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
    private $authorizationChecker;

    private $items;

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        if (!$this->items) {
            $this->items = array();

            if ($this->authorizationChecker->isGranted(ModeraBackendToolsActivityLogBundle::ROLE_ACCESS_BACKEND_TOOLS_ACTIVITY_LOG_SECTION)) {
                $this->items[] = new Section(
                    T::trans('Activity log'),
                    'tools.activitylog',
                    T::trans('See what activities recently have happened on the site'),
                    '', '',
                    'modera-backend-tools-activity-log-icon'
                );
            }
        }

        return $this->items;
    }
}

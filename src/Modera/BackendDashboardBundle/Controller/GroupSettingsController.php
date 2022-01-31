<?php

namespace Modera\BackendDashboardBundle\Controller;

use Modera\BackendDashboardBundle\Entity\GroupSettings;

/**
 * @author    Alex Rudakov <alexandr.rudakov@modera.org>
 * @copyright 2014 Modera Foundation
 */
class GroupSettingsController extends AbstractSettingsController
{
    protected function getEntityClass(): string
    {
        return GroupSettings::class;
    }
}

<?php

namespace Modera\BackendDashboardBundle\Controller;

use Modera\BackendDashboardBundle\Entity\UserSettings;

/**
 * @author    Alex Rudakov <alexandr.rudakov@modera.org>
 * @copyright 2014 Modera Foundation
 */
class UserSettingsController extends AbstractSettingsController
{
    protected function getEntityClass()
    {
        return UserSettings::clazz();
    }
}

<?php

namespace Modera\BackendDashboardBundle\Traits;

/**
 * @internal
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
trait DashboardSettingsTrait
{
    /**
     * Returned array must contain "hasAccess" key which contains ID of dashboard that associated with this
     * Trait entity has access to (user/users group).
     *
     * @return array
     */
    abstract public function getDashboardSettings();

    /**
     * @param string $dashboardId
     *
     * @return bool
     */
    public function hasAccessToDashboard($dashboardId)
    {
        $bs = $this->getDashboardSettings();

        return isset($bs['hasAccess']) && is_array($bs['hasAccess']) && in_array($dashboardId, $bs['hasAccess']);
    }

    /**
     * @return string|null
     */
    public function getDefaultDashboardId()
    {
        $bs = $this->getDashboardSettings();

        return isset($bs['defaultDashboard']) ? $bs['defaultDashboard'] : null;
    }
}

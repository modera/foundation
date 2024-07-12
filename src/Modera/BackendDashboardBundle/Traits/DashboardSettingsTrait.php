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
     * @return array<string, mixed>
     */
    abstract public function getDashboardSettings(): array;

    public function hasAccessToDashboard(string $dashboardId): bool
    {
        $settings = $this->getDashboardSettings();

        if (isset($settings['hasAccess']) && is_array($settings['hasAccess'])) {
            return in_array($dashboardId, $settings['hasAccess']);
        }

        return false;
    }

    public function getDefaultDashboardId(): ?string
    {
        $settings = $this->getDashboardSettings();

        if (isset($settings['defaultDashboard']) && is_string($settings['defaultDashboard'])) {
            return $settings['defaultDashboard'] ?: null;
        }

        return null;
    }
}

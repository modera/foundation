<?php

namespace Modera\BackendDashboardBundle\Service;

use Doctrine\ORM\EntityManager;
use Modera\BackendDashboardBundle\Dashboard\DashboardInterface;
use Modera\BackendDashboardBundle\Entity\GroupSettings;
use Modera\BackendDashboardBundle\Entity\UserSettings;
use Modera\SecurityBundle\Entity\User;
use Sli\ExpanderBundle\Ext\ContributorInterface;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
class DashboardManager
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var ContributorInterface
     */
    private $dashboardsProvider;

    /**
     * @var array
     */
    private $dashboards;

    /**
     * @param EntityManager $em
     * @param ContributorInterface $dashboardsProvider
     */
    public function __construct(EntityManager $em, ContributorInterface $dashboardsProvider)
    {
        $this->em = $em;
        $this->dashboardsProvider = $dashboardsProvider;
    }

    /**
     * @return DashboardInterface[]
     */
    public function getDashboards()
    {
        return $this->dashboardsProvider->getItems();
    }

    /**
     * @param string $name
     *
     * @return DashboardInterface|null
     */
    public function getDashboardByName($name)
    {
        if (!$this->dashboards) {
            $this->dashboards = array();
            foreach ($this->dashboardsProvider->getItems() as $dashboard) {
                /* @var DashboardInterface $dashboard */

                $this->dashboards[$dashboard->getName()] = $dashboard;
            }
        }

        return isset($this->dashboards[$name]) ? $this->dashboards[$name] : null;
    }

    /**
     * Finds all dashboards that given user has access to.
     *
     * @param User $user
     *
     * @return DashboardInterface[]
     */
    public function getUserDashboards(User $user)
    {
        $names = [];
        foreach ($this->getSettings($user) as $settingsEntry) {
            if (isset($settingsEntry['defaultDashboard']) && $settingsEntry['defaultDashboard']) {
                $names[] = $settingsEntry['defaultDashboard'];
            }

            if (isset($settingsEntry['hasAccess'])) {
                $names = array_merge($names, $settingsEntry['hasAccess']);
            }
        }

        $names = array_unique($names);

        $dashboards = [];
        foreach ($names as $name) {
            $dashboard = $this->getDashboardByName($name);
            if ($dashboard) {
                $dashboards[] = $dashboard;
            }
        }

        return $dashboards;
    }

    /**
     * Returns default dashboards that are configured for given user, usually you will want to take the first
     * from the list.
     *
     * @param User $user
     *
     * @return DashboardInterface[]
     */
    public function getDefaultDashboards(User $user)
    {
        $dashboards = [];

        foreach ($this->getSettings($user) as $settingsEntry) {
            if (isset($settingsEntry['defaultDashboard']) && $settingsEntry['defaultDashboard']) {
                $dashboard = $this->getDashboardByName($settingsEntry['defaultDashboard']);
                if ($dashboard) {
                    // helps to avoid possible duplicate dashboards
                    $dashboards[$dashboard->getName()] = $dashboard;
                }
            }
        }

        return array_values($dashboards);
    }

    /**
     * First settings entries returned are more specific.
     *
     * @param User $user
     *
     * @return array
     */
    private function getSettings(User $user)
    {
        $settings = [];

        /** @var UserSettings $userSettings */
        $userSettings = $this->em->getRepository(UserSettings::class)->findOneBy(array('user' => $user));
        if ($userSettings) {
            $settings[] = $userSettings->getDashboardSettings();
        }

        foreach ($user->getGroups() as $group) {
            /** @var GroupSettings $groupSettings */
            $groupSettings = $this->em->getRepository(GroupSettings::class)->findOneBy(array('group' => $group));
            if ($groupSettings) {
                $settings[] = $groupSettings->getDashboardSettings();
            }
        }

        return $settings;
    }
}
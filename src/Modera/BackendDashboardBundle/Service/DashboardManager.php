<?php

namespace Modera\BackendDashboardBundle\Service;

use Doctrine\ORM\EntityManager;
use Modera\BackendDashboardBundle\Dashboard\DashboardInterface;
use Modera\BackendDashboardBundle\Entity\GroupSettings;
use Modera\BackendDashboardBundle\Entity\UserSettings;
use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\SecurityBundle\Entity\User;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
class DashboardManager
{
    private EntityManager $em;

    private ContributorInterface $dashboardsProvider;

    /**
     * @var array<string, DashboardInterface>
     */
    private ?array $dashboards = null;

    public function __construct(EntityManager $em, ContributorInterface $dashboardsProvider)
    {
        $this->em = $em;
        $this->dashboardsProvider = $dashboardsProvider;
    }

    /**
     * @return DashboardInterface[]
     */
    public function getDashboards(): array
    {
        /** @var DashboardInterface[] $items */
        $items = $this->dashboardsProvider->getItems();

        return $items;
    }

    public function getDashboardByName(string $name): ?DashboardInterface
    {
        if (!$this->dashboards) {
            $this->dashboards = [];
            /** @var DashboardInterface $dashboard */
            foreach ($this->dashboardsProvider->getItems() as $dashboard) {
                $this->dashboards[$dashboard->getName()] = $dashboard;
            }
        }

        return $this->dashboards[$name] ?? null;
    }

    /**
     * Finds all dashboards that given user has access to.
     *
     * @return DashboardInterface[]
     */
    public function getUserDashboards(User $user): array
    {
        $names = [];
        foreach ($this->getSettings($user) as $settingsEntry) {
            if (isset($settingsEntry['defaultDashboard']) && $settingsEntry['defaultDashboard']) {
                $names[] = $settingsEntry['defaultDashboard'];
            }

            if (isset($settingsEntry['hasAccess']) && \is_array($settingsEntry['hasAccess'])) {
                $names = \array_merge($names, $settingsEntry['hasAccess']);
            }
        }

        $names = \array_unique($names);

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
     * @return DashboardInterface[]
     */
    public function getDefaultDashboards(User $user): array
    {
        $dashboards = [];

        foreach ($this->getSettings($user) as $settingsEntry) {
            if (isset($settingsEntry['defaultDashboard']) && \is_string($settingsEntry['defaultDashboard'])) {
                $dashboard = $this->getDashboardByName($settingsEntry['defaultDashboard']);
                if ($dashboard) {
                    // helps to avoid possible duplicate dashboards
                    $dashboards[$dashboard->getName()] = $dashboard;
                }
            }
        }

        return \array_values($dashboards);
    }

    /**
     * First settings entries returned are more specific.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getSettings(User $user): array
    {
        $settings = [];

        /** @var ?UserSettings $userSettings */
        $userSettings = $this->em->getRepository(UserSettings::class)->findOneBy(['user' => $user]);
        if ($userSettings) {
            $settings[] = $userSettings->getDashboardSettings();
        }

        foreach ($user->getGroups() as $group) {
            /** @var ?GroupSettings $groupSettings */
            $groupSettings = $this->em->getRepository(GroupSettings::class)->findOneBy(['group' => $group]);
            if ($groupSettings) {
                $settings[] = $groupSettings->getDashboardSettings();
            }
        }

        return $settings;
    }
}

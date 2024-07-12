<?php

namespace Modera\BackendDashboardBundle\Contributions;

use Doctrine\ORM\EntityManager;
use Modera\BackendDashboardBundle\Dashboard\DashboardInterface;
use Modera\BackendDashboardBundle\Dashboard\SimpleDashboard;
use Modera\BackendDashboardBundle\Entity\UserSettings;
use Modera\BackendDashboardBundle\Service\DashboardManager;
use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\MjrIntegrationBundle\Config\ConfigMergerInterface;
use Modera\SecurityBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Adds dashboard list to config for backend. It allows to show dashboards immediately without loading remote data
 * through Direct.
 *
 * @internal
 *
 * @author    Alex Rudakov <alexandr.rudakov@modera.org>
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class ConfigMergersProvider implements ContributorInterface, ConfigMergerInterface
{
    private ContainerInterface $container;

    private ContributorInterface $dashboardProvider;

    private DashboardManager $dashboardMgr;

    private TokenStorageInterface $tokenStorage;

    /**
     * @internal
     */
    public function __construct(
        ContainerInterface $container,
        ContributorInterface $dashboardProvider,
        TokenStorageInterface $tokenStorage,
        DashboardManager $dashboardMgr
    ) {
        $this->container = $container;
        $this->dashboardProvider = $dashboardProvider;
        $this->tokenStorage = $tokenStorage;
        $this->dashboardMgr = $dashboardMgr;
    }

    /**
     * Merge in dashboard list into runtime configuration.
     */
    public function merge(array $existingConfig): array
    {
        $result = [];

        $token = $this->tokenStorage->getToken();
        if ($token && $user = $token->getUser()) {
            /** @var User $user */
            $defaultDashboardNames = [];
            foreach ($this->dashboardMgr->getDefaultDashboards($user) as $dashboard) {
                $defaultDashboardNames[] = $dashboard->getName();
            }

            $isDefaultFound = false;

            foreach ($this->dashboardMgr->getUserDashboards($user) as $dashboard) {
                if (!$dashboard->isAllowed($this->container)) {
                    continue;
                }

                $isDefault = \in_array($dashboard->getName(), $defaultDashboardNames);
                if ($isDefault) {
                    $isDefaultFound = true;
                }

                $result[] = \array_merge($this->serializeDashboard($dashboard), [
                    'default' => $isDefault,
                ]);
            }

            if (!$isDefaultFound) {
                // if there's no default dashboard available for a given user then we will display a dashboard
                // where user will be able to pick one he/she needs
                $dashboard = new SimpleDashboard(
                    'default',
                    'List of user dashboards',
                    'Modera.backend.dashboard.runtime.DashboardListDashboardActivity'
                );

                $result[] = \array_merge($this->serializeDashboard($dashboard), [
                    'default' => true,
                ]);
            }
        }

        return \array_merge($existingConfig, [
            'homeSection' => $this->getUserLandingSection(),
            'modera_backend_dashboard' => [
                'dashboards' => $result,
            ],
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function serializeDashboard(DashboardInterface $dashboard): array
    {
        return [
            'name' => $dashboard->getName(),
            'label' => $dashboard->getLabel(),
            'uiClass' => $dashboard->getUiClass(),
            'iconCls' => $dashboard->getIcon(),
            'description' => $dashboard->getDescription(),
        ];
    }

    public function getItems(): array
    {
        return [$this];
    }

    /**
     * Return dashboardProvider.
     */
    public function getDashboardProvider(): ContributorInterface
    {
        return $this->dashboardProvider;
    }

    /**
     * @internal
     */
    public function getUserLandingSection(): string
    {
        $landingSection = 'dashboard';

        /** @var ?EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');

        if ($em && ($token = $this->tokenStorage->getToken()) && $user = $token->getUser()) {
            /** @var User $user */
            /** @var UserSettings|null $userSettings */
            $userSettings = $em->getRepository(UserSettings::class)->findOneBy(['user' => $user]);

            if ($userSettings) {
                $settings = $userSettings->getDashboardSettings();
                if (isset($settings['landingSection']) && \is_string($settings['landingSection'])) {
                    $landingSection = $settings['landingSection'];
                }
            }
        }

        return $landingSection;
    }
}

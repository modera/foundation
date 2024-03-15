<?php

namespace Modera\BackendDashboardBundle\Controller;

use Modera\BackendDashboardBundle\Dashboard\DashboardInterface;
use Modera\BackendDashboardBundle\Entity\SettingsEntityInterface;
use Modera\BackendSecurityBundle\ModeraBackendSecurityBundle;
use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\ServerCrudBundle\Controller\AbstractCrudController;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author    Alex Rudakov <alexandr.rudakov@modera.org>
 * @copyright 2014 Modera Foundation
 */
abstract class AbstractSettingsController extends AbstractCrudController
{
    public function getConfig(): array
    {
        return [
            'entity' => $this->getEntityClass(),
            'security' => [
                'role' => ModeraBackendSecurityBundle::ROLE_MANAGE_USER_PROFILES,
            ],
            'hydration' => [
                'groups' => [
                    'main' => function ($settings) {
                        return $this->hydrateSettings($settings);
                    },
                ],
                'profiles' => [
                    'main',
                ],
            ],
            'map_data_on_update' => function ($params, $entity, $defaultMapper) {
                $this->mapEntityOnUpdate($params, $entity);
            },
        ];
    }

    abstract protected function getEntityClass(): string;

    /**
     * @param array<mixed> $params
     */
    private function mapEntityOnUpdate(array $params, SettingsEntityInterface $entity): void
    {
        if (isset($params['dashboards']) && \is_array($params['dashboards'])) {
            $dashboardSettings = [
                'hasAccess' => [],
                'defaultDashboard' => null,
                'landingSection' => $params['landingSection'] ?? 'dashboard',
            ];

            foreach ($params['dashboards'] as $dashboard) {
                if (isset($dashboard['hasAccess']) && isset($dashboard['id']) && isset($dashboard['isDefault'])) {
                    if (true === $dashboard['isDefault']) {
                        $dashboardSettings['hasAccess'][] = $dashboard['id'];
                        $dashboardSettings['defaultDashboard'] = $dashboard['id'];

                        continue;
                    }

                    if (true === $dashboard['hasAccess']) {
                        $dashboardSettings['hasAccess'][] = $dashboard['id'];
                    }
                }
            }

            $entity->setDashboardSettings($dashboardSettings);
        }
    }

    private function getDashboardProvider(): ContributorInterface
    {
        /** @var ContributorInterface $provider */
        $provider = $this->container->get('modera_backend_dashboard.dashboard_provider');

        return $provider;
    }

    /**
     * @return array<string, mixed>
     */
    private function hydrateSettings(SettingsEntityInterface $e): array
    {
        $dashboards = [];
        foreach ($this->getDashboardProvider()->getItems() as $dashboard) {
            /** @var DashboardInterface $dashboard */

            /** @var ContainerInterface $container */
            $container = $this->container;
            if (!$dashboard->isAllowed($container)) {
                continue;
            }

            $dashboards[] = [
                'id' => $dashboard->getName(),
                'name' => $dashboard->getLabel(),
            ];
        }

        /** @var array{hasAccess: string[], defaultDashboard: ?string, landingSection?: ?string} $userDashboardSettings */
        $userDashboardSettings = $e->getDashboardSettings();

        $preparedDashboardSettings = [];
        foreach ($dashboards as $dashboard) {
            $preparedDashboardSettings[] = \array_merge(
                $dashboard,
                [
                    'hasAccess' => \in_array($dashboard['id'], $userDashboardSettings['hasAccess']),
                    'isDefault' => $dashboard['id'] == $userDashboardSettings['defaultDashboard'],
                ]
            );
        }

        $landingSection = 'dashboard';
        if (isset($userDashboardSettings['landingSection'])) {
            $landingSection = $userDashboardSettings['landingSection'];
        }

        return [
            'id' => $e->getId(),
            'title' => $e->describeEntity(),
            'landingSection' => $landingSection,
            'dashboardSettings' => $preparedDashboardSettings,
        ];
    }
}

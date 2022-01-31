<?php

namespace Modera\BackendDashboardBundle\Controller;

use Sli\ExpanderBundle\Ext\ContributorInterface;
use Modera\BackendSecurityBundle\ModeraBackendSecurityBundle;
use Modera\ServerCrudBundle\Controller\AbstractCrudController;
use Modera\BackendDashboardBundle\Dashboard\DashboardInterface;
use Modera\BackendDashboardBundle\Entity\SettingsEntityInterface;

/**
 * @author    Alex Rudakov <alexandr.rudakov@modera.org>
 * @copyright 2014 Modera Foundation
 */
abstract class AbstractSettingsController extends AbstractCrudController
{
    /**
     * {@inheritdoc}
     */
    public function getConfig(): array
    {
        return array(
            'entity' => $this->getEntityClass(),
            'security' => array(
                'role' => ModeraBackendSecurityBundle::ROLE_MANAGE_USER_PROFILES
            ),
            'hydration' => array(
                'groups' => array(
                    'main' => function ($settings) {
                        return $this->hydrateSettings($settings);
                    },
                ),
                'profiles' => array(
                    'main',
                ),
            ),
            'map_data_on_update' => function ($params, $entity, $defaultMapper) {
                $this->mapEntityOnUpdate($params, $entity, $defaultMapper);
            },
        );
    }

    /**
     * @return string
     */
    abstract protected function getEntityClass(): string;

    private function mapEntityOnUpdate(array $params, SettingsEntityInterface $entity)
    {
        if (isset($params['dashboards']) && is_array($params['dashboards'])) {
            $dashboardSettings = array(
                'hasAccess' => array(),
                'defaultDashboard' => null,
                'landingSection' => isset($params['landingSection']) ? $params['landingSection'] : 'dashboard',
            );

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
        return $this->get('modera_backend_dashboard.dashboard_provider');
    }

    private function hydrateSettings(SettingsEntityInterface $e): array
    {
        $dashboards = array();
        foreach ($this->getDashboardProvider()->getItems() as $dashboard) {
            /* @var DashboardInterface $dashboard */

            if (!$dashboard->isAllowed($this->container)) {
                continue;
            }

            $dashboards[] = array(
                'id' => $dashboard->getName(),
                'name' => $dashboard->getLabel(),
            );
        }

        $userDashboardSettings = $e->getDashboardSettings();

        $preparedDashboardSettings = array();
        foreach ($dashboards as $dashboard) {
            $preparedDashboardSettings[] = array_merge(
                $dashboard,
                array(
                    'hasAccess' => in_array($dashboard['id'], $userDashboardSettings['hasAccess']),
                    'isDefault' => $dashboard['id'] == $userDashboardSettings['defaultDashboard'],
                )
            );
        }

        $landingSection = 'dashboard';
        if (isset($userDashboardSettings['landingSection'])) {
            $landingSection = $userDashboardSettings['landingSection'];
        }

        return array(
            'id' => $e->getId(),
            'title' => $e->describeEntity(),
            'landingSection' => $landingSection,
            'dashboardSettings' => $preparedDashboardSettings,
        );
    }
}

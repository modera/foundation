<?php

namespace Modera\BackendDashboardBundle\Contributions;

use Doctrine\ORM\EntityManager;
use Modera\BackendDashboardBundle\Dashboard\DashboardInterface;
use Modera\BackendDashboardBundle\Dashboard\SimpleDashboard;
use Modera\BackendDashboardBundle\Entity\GroupSettings;
use Modera\BackendDashboardBundle\Entity\UserSettings;
use Modera\BackendDashboardBundle\Service\DashboardManager;
use Modera\MjrIntegrationBundle\Config\ConfigMergerInterface;
use Modera\SecurityBundle\Entity\User;
use Sli\ExpanderBundle\Ext\ContributorInterface;
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
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var ContributorInterface
     */
    private $dashboardProvider;

    /**
     * @var DashboardManager
     */
    private $dashboardMgr;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @internal
     *
     * @param ContainerInterface    $container         Symfony container for isAllowed() method
     * @param ContributorInterface  $dashboardProvider
     * @param TokenStorageInterface $tokenStorage
     * @param DashboardManager      $dashboardMgr
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
     *
     * {@inheritdoc}
     */
    public function merge(array $currentConfig)
    {
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();

        $defaultDashboardNames = [];
        foreach ($this->dashboardMgr->getDefaultDashboards($user) as $dashboard) {
            $defaultDashboardNames[] = $dashboard->getName();
        }

        $isDefaultFound = false;

        $result = array();
        foreach ($this->dashboardMgr->getUserDashboards($user) as $dashboard) {
            if (!$dashboard->isAllowed($this->container)) {
                continue;
            }

            $isDefault = in_array($dashboard->getName(), $defaultDashboardNames);
            if ($isDefault) {
                $isDefaultFound = true;
            }

            $result[] = array_merge($this->serializeDashboard($dashboard), array(
                'default' => $isDefault,
            ));
        }

        if (!$isDefaultFound) {
            // if there's no default dashboard available for a given user then we will display a dashboard
            // where user will be able to pick one he/she needs
            $dashboard = new SimpleDashboard(
                'default',
                'List of user dashboards',
                'Modera.backend.dashboard.runtime.DashboardListDashboardActivity'
            );

            $result[] = array_merge($this->serializeDashboard($dashboard), array(
                'default' => true,
            ));
        }

        return array_merge($currentConfig, array(
            'homeSection' => $this->getUserLandingSection(),
            'modera_backend_dashboard' => array(
                'dashboards' => $result,
            ),
        ));
    }

    /**
     * @param DashboardInterface $dashboard
     *
     * @return array
     */
    private function serializeDashboard(DashboardInterface $dashboard)
    {
        return array(
            'name' => $dashboard->getName(),
            'label' => $dashboard->getLabel(),
            'uiClass' => $dashboard->getUiClass(),
            'iconCls' => $dashboard->getIcon(),
            'description' => $dashboard->getDescription(),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        return array($this);
    }

    /**
     * Return dashboardProvider.
     *
     * @return mixed
     */
    public function getDashboardProvider()
    {
        return $this->dashboardProvider;
    }

    /**
     * @deprecated  Use DashboardManager class methods instead
     *
     * @return array
     */
    public function getUserDashboards()
    {
        /** @var EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $this->container->get('security.token_storage')->getToken()->getUser();

        $settings = [];
        foreach ($user->getGroups() as $group) {
            /** @var GroupSettings $groupSettings */
            $groupSettings = $em->getRepository(GroupSettings::class)->findOneBy(array('group' => $group));
            if ($groupSettings) {
                $settings[] = $groupSettings->getDashboardSettings();
            }
        }
        /** @var UserSettings $userSettings */
        $userSettings = $em->getRepository(UserSettings::class)->findOneBy(array('user' => $user));
        if ($userSettings) {
            $settings[] = $userSettings->getDashboardSettings();
        }

        $dashboards = [];
        $defaults = [];

        foreach ($settings as $data) {
            $dashboards = array_merge($dashboards, $data['hasAccess']);
            if ($data['defaultDashboard']) {
                $defaults[] = $data['defaultDashboard'];
            }
        }

        if (!count($dashboards)) {
            $dashboards = [];
            $default = null;
        } else {
            $default = count($defaults) ? $defaults[count($defaults) - 1] : null;
        }

        return [$default, $dashboards];
    }

    /**
     * @internal
     *
     * @return string
     */
    public function getUserLandingSection()
    {
        $landingSection = 'dashboard';

        /** @var EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');

        if ($em) {
            /** @var User $user */
            $user = $this->tokenStorage->getToken()->getUser();

            /** @var UserSettings $userSettings */
            $userSettings = $em->getRepository(UserSettings::class)->findOneBy(array('user' => $user));

            if ($userSettings) {
                $settings = $userSettings->getDashboardSettings();
                if (isset($settings['landingSection'])) {
                    $landingSection = $settings['landingSection'];
                }
            }
        }

        return $landingSection;
    }
}

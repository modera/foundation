<?php

namespace Modera\BackendDashboardBundle\Entity;

use Modera\BackendDashboardBundle\Traits\DashboardSettingsTrait;
use Modera\SecurityBundle\Entity\Group;
use Doctrine\ORM\Mapping as ORM;

/**
 * @internal
 *
 * @author    Alex Rudakov <alexandr.rudakov@modera.org>
 * @copyright 2014 Modera Foundation
 *
 * @ORM\Entity
 * @ORM\Table(name="modera_dashboard_groupdashboardsettings")
 */
class GroupSettings implements SettingsEntityInterface
{
    use DashboardSettingsTrait;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Group
     *
     * @Orm\OneToOne(targetEntity="Modera\SecurityBundle\Entity\Group")
     */
    private $group;

    /**
     * @ORM\Column(type="array")
     */
    private $dashboardSettings = array(
        'defaultDashboard' => null,
        'hasAccess' => [], // contains "names" of dashboard given group will have access to
    );

    /**
     * @deprecated Use native ::class property
     *
     * @return string
     */
    public static function clazz()
    {
        @trigger_error(sprintf(
            'The "%s()" method is deprecated. Use native ::class property.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        return get_called_class();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function setDashboardSettings(array $dashboardSettings)
    {
        $this->dashboardSettings = $dashboardSettings;
    }

    /**
     * {@inheritdoc}
     */
    public function getDashboardSettings()
    {
        return $this->dashboardSettings;
    }

    /**
     * @param \Modera\SecurityBundle\Entity\Group $group
     */
    public function setGroup($group)
    {
        $this->group = $group;
    }

    /**
     * @return \Modera\SecurityBundle\Entity\Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @return array
     */
    public function describeEntity()
    {
        return sprintf('Group "%s"', $this->getGroup()->getName());
    }
}

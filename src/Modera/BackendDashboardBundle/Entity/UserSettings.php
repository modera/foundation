<?php

namespace Modera\BackendDashboardBundle\Entity;

use Modera\BackendDashboardBundle\Traits\DashboardSettingsTrait;
use Modera\SecurityBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @internal
 *
 * @author    Alex Rudakov <alexandr.rudakov@modera.org>
 * @copyright 2014 Modera Foundation
 *
 * @ORM\Entity
 * @ORM\Table(name="modera_dashboard_userdashboardsettings")
 */
class UserSettings implements SettingsEntityInterface
{
    use DashboardSettingsTrait;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var User
     *
     * @Orm\OneToOne(targetEntity="Modera\SecurityBundle\Entity\User")
     */
    private $user;

    /**
     * @ORM\Column(type="array")
     */
    private $dashboardSettings = array(
        'defaultDashboard' => null,
        'hasAccess' => [], // contains "names" of dashboard given user will have access to
    );

    /**
     * @deprecated Use native ::class property.
     *
     * @return string
     */
    public static function clazz()
    {
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
     * @param \Modera\SecurityBundle\Entity\User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return \Modera\SecurityBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * {@inheritdoc}
     */
    public function describeEntity()
    {
        return sprintf('User "%s"', $this->getUser()->getUsername());
    }
}

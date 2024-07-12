<?php

namespace Modera\BackendDashboardBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Modera\BackendDashboardBundle\Traits\DashboardSettingsTrait;
use Modera\SecurityBundle\Entity\Group;

/**
 * @internal
 *
 * @author    Alex Rudakov <alexandr.rudakov@modera.org>
 * @copyright 2014 Modera Foundation
 *
 * @ORM\Entity
 *
 * @ORM\Table(name="modera_dashboard_groupdashboardsettings")
 */
class GroupSettings implements SettingsEntityInterface
{
    use DashboardSettingsTrait;

    /**
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\OneToOne(targetEntity="Modera\SecurityBundle\Entity\Group")
     */
    private ?Group $group = null;

    /**
     * @var array<string, mixed>
     *
     * @ORM\Column(type="json")
     */
    private $dashboardSettings = [
        'defaultDashboard' => null,
        'hasAccess' => [], // contains "names" of dashboard given group will have access to
    ];

    /**
     * @deprecated Use native ::class property
     */
    public static function clazz(): string
    {
        @\trigger_error(\sprintf(
            'The "%s()" method is deprecated. Use native ::class property.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        return \get_called_class();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setDashboardSettings(array $settings): void
    {
        $this->dashboardSettings = $settings;
    }

    public function getDashboardSettings(): array
    {
        return $this->dashboardSettings;
    }

    public function setGroup(Group $group): void
    {
        $this->group = $group;
    }

    public function getGroup(): ?Group
    {
        return $this->group;
    }

    public function describeEntity(): string
    {
        return sprintf('Group "%s"', $this->getGroup() ? $this->getGroup()->getName() : '-');
    }
}

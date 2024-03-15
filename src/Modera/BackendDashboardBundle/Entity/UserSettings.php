<?php

namespace Modera\BackendDashboardBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Modera\BackendDashboardBundle\Traits\DashboardSettingsTrait;
use Modera\SecurityBundle\Entity\User;

/**
 * @internal
 *
 * @author    Alex Rudakov <alexandr.rudakov@modera.org>
 * @copyright 2014 Modera Foundation
 *
 * @ORM\Entity
 *
 * @ORM\Table(name="modera_dashboard_userdashboardsettings")
 */
class UserSettings implements SettingsEntityInterface
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
     * @ORM\OneToOne(targetEntity="Modera\SecurityBundle\Entity\User")
     */
    private ?User $user = null;

    /**
     * @var array<string, mixed>
     *
     * @ORM\Column(type="json")
     */
    private $dashboardSettings = [
        'defaultDashboard' => null,
        'hasAccess' => [], // contains "names" of dashboard given user will have access to
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

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function describeEntity(): string
    {
        return sprintf('User "%s"', $this->getUser() ? $this->getUser()->getUsername() : '-');
    }
}

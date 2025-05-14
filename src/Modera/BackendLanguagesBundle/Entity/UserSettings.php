<?php

namespace Modera\BackendLanguagesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Modera\LanguagesBundle\Entity\Language;
use Modera\SecurityBundle\Entity\User;

/**
 * @copyright 2014 Modera Foundation
 */
#[ORM\Entity]
#[ORM\Table(name: 'modera_backendlanguages_usersettings')]
class UserSettings
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: User::class)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Language::class)]
    private ?Language $language = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user = null): void
    {
        $this->user = $user;
    }

    public function getLanguage(): ?Language
    {
        return $this->language;
    }

    public function setLanguage(?Language $language = null): void
    {
        $this->language = $language;
    }
}

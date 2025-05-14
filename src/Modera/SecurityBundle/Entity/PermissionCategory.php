<?php

namespace Modera\SecurityBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @copyright 2014 Modera Foundation
 */
#[ORM\Entity]
#[ORM\Table(name: 'modera_security_permissioncategory')]
class PermissionCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string')]
    private string $name;

    #[ORM\Column(type: 'string')]
    private string $technicalName;

    #[ORM\Column(type: 'integer')]
    private int $position = 0;

    /**
     * @var Collection<int, Permission>
     */
    #[ORM\OneToMany(targetEntity: Permission::class, mappedBy: 'category', cascade: ['persist'])]
    private Collection $permissions;

    public function __construct(?string $name = null, ?string $technicalName = null)
    {
        if ($name) {
            $this->name = $name;
        }

        if ($technicalName) {
            $this->technicalName = $technicalName;
        }

        $this->permissions = new ArrayCollection();
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setTechnicalName(string $technicalName): void
    {
        $this->technicalName = $technicalName;
    }

    public function getTechnicalName(): string
    {
        return $this->technicalName;
    }

    /**
     * @param Collection<int, Permission> $permissions
     */
    public function setPermissions(Collection $permissions): void
    {
        $this->permissions = $permissions;
    }

    /**
     * @return Collection<int, Permission>
     */
    public function getPermissions(): Collection
    {
        return $this->permissions;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}

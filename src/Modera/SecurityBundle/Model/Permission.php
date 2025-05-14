<?php

namespace Modera\SecurityBundle\Model;

/**
 * @copyright 2014 Modera Foundation
 */
class Permission implements PermissionInterface
{
    public function __construct(
        /**
         * @see PermissionInterface::getName()
         */
        private readonly string $name,
        /**
         * @see PermissionInterface::getRole()
         */
        private readonly string $role,
        /**
         * @see PermissionInterface::getCategory()
         */
        private readonly ?string $category = null,
        /**
         * @see PermissionInterface::getDescription()
         */
        private readonly ?string $description = null,
    ) {
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }
}

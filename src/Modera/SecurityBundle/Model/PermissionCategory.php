<?php

namespace Modera\SecurityBundle\Model;

/**
 * @copyright 2014 Modera Foundation
 */
class PermissionCategory implements PermissionCategoryInterface
{
    public function __construct(
        private readonly string $name,
        private readonly string $technicalName,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTechnicalName(): string
    {
        return $this->technicalName;
    }
}

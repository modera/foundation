<?php

namespace Modera\ServerCrudBundle\Hydration;

/**
 * @copyright 2013 Modera Foundation
 */
class UnknownHydrationGroupException extends \RuntimeException
{
    private ?string $groupName = null;

    public function setGroupName(string $groupName): void
    {
        $this->groupName = $groupName;
    }

    public function getGroupName(): ?string
    {
        return $this->groupName;
    }
}

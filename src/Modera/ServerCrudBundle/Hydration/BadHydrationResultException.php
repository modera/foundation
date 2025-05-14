<?php

namespace Modera\ServerCrudBundle\Hydration;

/**
 * @copyright 2016 Modera Foundation
 */
class BadHydrationResultException extends \RuntimeException
{
    private mixed $result;

    private ?HydrationProfile $profile = null;

    private ?string $groupName = null;

    public static function create(
        string $message,
        mixed $result = null,
        ?HydrationProfile $profile = null,
        ?string $groupName = null,
    ): self {
        $me = new self($message);
        $me->result = $result;
        $me->profile = $profile;
        $me->groupName = $groupName;

        return $me;
    }

    public function getResult(): mixed
    {
        return $this->result;
    }

    public function getProfile(): ?HydrationProfile
    {
        return $this->profile;
    }

    public function getGroupName(): ?string
    {
        return $this->groupName;
    }
}

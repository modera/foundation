<?php

namespace Modera\ServerCrudBundle\Hydration;

/**
 * @copyright 2013 Modera Foundation
 */
class UnknownHydrationProfileException extends \RuntimeException
{
    private ?string $profileName = null;

    public function setProfileName(string $profileName): void
    {
        $this->profileName = $profileName;
    }

    public function getProfileName(): ?string
    {
        return $this->profileName;
    }
}

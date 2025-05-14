<?php

namespace Modera\ServerCrudBundle\Security;

/**
 * @copyright 2014 Modera Foundation
 */
class AccessDeniedHttpException extends \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
{
    private ?string $role = null;

    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }
}

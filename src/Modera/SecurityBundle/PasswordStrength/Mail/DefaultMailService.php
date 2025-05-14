<?php

namespace Modera\SecurityBundle\PasswordStrength\Mail;

use Modera\SecurityBundle\Entity\UserInterface;

/**
 * @copyright 2022 Modera Foundation
 */
class DefaultMailService implements MailServiceInterface
{
    public function sendPassword(UserInterface $user, string $plainPassword): void
    {
        // do nothing
    }
}

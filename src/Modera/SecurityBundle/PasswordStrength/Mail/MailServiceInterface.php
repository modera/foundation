<?php

namespace Modera\SecurityBundle\PasswordStrength\Mail;

use Modera\SecurityBundle\Entity\UserInterface;

/**
 * @copyright 2017 Modera Foundation
 */
interface MailServiceInterface
{
    public function sendPassword(UserInterface $user, string $plainPassword): void;
}

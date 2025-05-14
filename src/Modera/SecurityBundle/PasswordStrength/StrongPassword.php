<?php

namespace Modera\SecurityBundle\PasswordStrength;

use Symfony\Component\Validator\Constraint;

/**
 * Use PasswordManager::validatePassword() instead for now. This attribute has been added as a part
 * of an initial design of the password-strength package, but it is clear now that the design could
 * have been simplified. If you need to use this attribute, let me know, otherwise after some
 * time if nobody still needs to use it, it will be removed.
 *
 * @internal
 *
 * @copyright 2017 Modera Foundation
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class StrongPassword extends Constraint
{
}

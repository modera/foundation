<?php

namespace Modera\SecurityBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @internal
 *
 * @copyright 2017 Modera Foundation
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class Email extends Constraint
{
    public string $service = 'modera_security.validator.email';

    public function validatedBy(): string
    {
        return $this->service;
    }
}

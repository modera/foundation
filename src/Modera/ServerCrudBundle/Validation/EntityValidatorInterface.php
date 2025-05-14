<?php

namespace Modera\ServerCrudBundle\Validation;

/**
 * @copyright 2014 Modera Foundation
 */
interface EntityValidatorInterface
{
    /**
     * @param array<string, mixed> $config
     */
    public function validate(object $entity, array $config): ValidationResult;
}

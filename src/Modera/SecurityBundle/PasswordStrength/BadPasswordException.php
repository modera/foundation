<?php

namespace Modera\SecurityBundle\PasswordStrength;

/**
 * @copyright 2017 Modera Foundation
 */
class BadPasswordException extends \RuntimeException
{
    /**
     * @var string[]
     */
    private array $errors = [];

    /**
     * @param string[] $errors
     */
    public function setErrors(array $errors = []): void
    {
        $this->errors = $errors;
    }

    /**
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}

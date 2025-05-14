<?php

namespace Modera\ServerCrudBundle\ExceptionHandling;

/**
 * @copyright 2014 Modera Foundation
 */
class BypassExceptionHandler implements ExceptionHandlerInterface
{
    public function createResponse(\Exception $e, string $operation): array
    {
        throw $e;
    }
}

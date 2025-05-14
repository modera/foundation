<?php

namespace Modera\DirectBundle\Controller;

use Modera\DirectBundle\Exception\CallException;

/**
 * @copyright 2021 Modera Foundation
 */
trait ControllerTrait
{
    protected function createDirectCallException(string $message = '', ?\Throwable $previous = null): CallException
    {
        return new CallException($message, $previous);
    }
}

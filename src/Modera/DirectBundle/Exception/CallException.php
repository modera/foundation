<?php

namespace Modera\DirectBundle\Exception;

/**
 * @copyright 2021 Modera Foundation
 */
class CallException extends \RuntimeException
{
    public function __construct(string $message = '', ?\Throwable $previous = null, int $code = 0)
    {
        parent::__construct($message, $code, $previous);
    }
}

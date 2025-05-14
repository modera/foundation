<?php

namespace Modera\ServerCrudBundle\DataMapping\MethodInvocation;

/**
 * @copyright 2024 Modera Foundation
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
final class Params
{
    /**
     * @param string[] $data
     */
    public function __construct(
        public readonly array $data,
    ) {
    }
}

<?php

namespace Modera\ServerCrudBundle\DataMapping\MethodInvocation;

/**
 * @copyright 2024 Modera Foundation
 */
interface MethodInvocationParametersProviderInterface
{
    /**
     * @return array<?object>
     */
    public function getParameters(string $fqcn, string $methodName): array;
}

<?php

namespace Modera\DynamicallyConfigurableMJRBundle\Resolver;

/**
 * @copyright 2021 Modera Foundation
 */
interface ValueResolverInterface
{
    public function resolve(string $name, mixed $value): mixed;
}

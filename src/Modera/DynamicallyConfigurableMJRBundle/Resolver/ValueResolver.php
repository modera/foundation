<?php

namespace Modera\DynamicallyConfigurableMJRBundle\Resolver;

/**
 * @copyright 2021 Modera Foundation
 */
class ValueResolver implements ValueResolverInterface
{
    public function resolve(string $name, mixed $value): mixed
    {
        return $value;
    }
}

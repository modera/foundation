<?php

namespace Modera\DynamicallyConfigurableMJRBundle\Resolver;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2021 Modera Foundation
 */
class ValueResolver implements ValueResolverInterface
{
    public function resolve(string $name, $value)
    {
        return $value;
    }
}

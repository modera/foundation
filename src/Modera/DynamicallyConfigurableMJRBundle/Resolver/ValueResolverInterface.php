<?php

namespace Modera\DynamicallyConfigurableMJRBundle\Resolver;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2021 Modera Foundation
 */
interface ValueResolverInterface
{
    /**
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public function resolve($name, $value);
}

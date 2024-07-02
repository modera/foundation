<?php

namespace Modera\DynamicallyConfigurableMJRBundle\Resolver;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2021 Modera Foundation
 */
interface ValueResolverInterface
{
    /**
     * @param mixed $value Mixed value
     *
     * @return mixed Mixed value
     */
    public function resolve(string $name, $value);
}

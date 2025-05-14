<?php

namespace Modera\ServerCrudBundle\Util;

/**
 * @copyright 2024 Modera Foundation
 */
interface ObjectFieldsManagerInterface
{
    /**
     * @param mixed[] $args Values a getter method must be invoked with. Each element of the array will correspond
     *                      to argument of the method.
     */
    public function get(object $object, string $key, array $args = []): mixed;

    /**
     * @param mixed[] $args Values a setter method must be invoked with. Each element of the array will correspond
     *                      to argument of the method.
     */
    public function set(object $object, string $key, array $args = []): mixed;
}

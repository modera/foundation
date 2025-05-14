<?php

namespace Modera\DynamicallyConfigurableAppBundle;

/**
 * @copyright 2018 Modera Foundation
 */
interface KernelConfigInterface
{
    /**
     * @param array{'debug'?: bool, 'env'?: string} $mode
     */
    public static function write(array $mode): void;

    /**
     * @return array{'debug': bool, 'env': string}
     */
    public static function read(): array;
}

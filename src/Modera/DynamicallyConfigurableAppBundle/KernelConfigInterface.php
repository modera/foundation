<?php

namespace Modera\DynamicallyConfigurableAppBundle;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
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

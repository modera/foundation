<?php

namespace Modera\DynamicallyConfigurableAppBundle;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2018 Modera Foundation
 */
interface KernelConfigInterface
{
    /**
     * @param array $mode
     */
    public static function write(array $mode);

    /**
     * @return array
     */
    public static function read(): array;
}

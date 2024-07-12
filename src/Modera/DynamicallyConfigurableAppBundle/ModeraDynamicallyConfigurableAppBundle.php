<?php

namespace Modera\DynamicallyConfigurableAppBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class ModeraDynamicallyConfigurableAppBundle extends Bundle
{
    public const CONFIG_KERNEL_ENV = 'kernel_env';
    public const CONFIG_KERNEL_DEBUG = 'kernel_debug';
}

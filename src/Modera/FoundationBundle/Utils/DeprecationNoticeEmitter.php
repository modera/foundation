<?php

namespace Modera\FoundationBundle\Utils;

/**
 * @internal
 *
 * @experimental
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2017 Modera Foundation
 */
class DeprecationNoticeEmitter
{
    public function emit(string $notice): void
    {
        @\trigger_error($notice, E_USER_DEPRECATED);
    }
}

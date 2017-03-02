<?php

namespace Modera\FoundationBundle\Utils;

/**
 * @internal
 * @experimental
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2017 Modera Foundation
 */
class DeprecationNoticeEmitter
{
    /**
     * @param string $notice
     */
    public function emit($notice)
    {
        @trigger_error($notice, E_USER_DEPRECATED);
    }
}
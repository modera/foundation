<?php

namespace Modera\TranslationsBundle\Tests\Fixtures\SecondBundle\Resources;

use Modera\FoundationBundle\Translation\T;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class Dummy
{
    public function test()
    {
        $test = T::trans('Test token');
        return T::trans('This token is only in SecondDummy bundle');
    }
}

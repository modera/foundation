<?php

namespace Modera\TranslationsBundle\Tests\Fixtures\SecondBundle\Resources;

use Modera\FoundationBundle\Translation\T;

class Dummy
{
    public function test(): string
    {
        $test = T::trans('Test token');

        return T::trans('This token is only in SecondDummy bundle');
    }
}

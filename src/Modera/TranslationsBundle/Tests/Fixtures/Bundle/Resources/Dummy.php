<?php

namespace Modera\TranslationsBundle\Tests\Fixtures\Bundle\Resources;

use Modera\FoundationBundle\Translation\T;

class Dummy
{
    public function test(): string
    {
        return T::trans('Test token');
    }
}

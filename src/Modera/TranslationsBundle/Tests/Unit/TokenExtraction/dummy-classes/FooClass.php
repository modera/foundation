<?php

namespace Modera\TranslationsBundle\Tests\Unit\TokenExtraction;

use Modera\FoundationBundle\Translation\T;

class FooClass
{
    public function method1(): void
    {
        T::trans('Broken domain', [], \rand(100));

        T::trans('Default domain', [], null);

        $barDomain = 'bardomain';

        T::trans('Some simple token');

        $message = 'hello ';
        $message .= 'world';

        T::trans('We got something for ya, %s!', ['name' => 'Vassily', 'xxx' => $fn()], 'foodomain');

        T::trans('Another token', [], $barDomain);

        T::trans('Another %value%', ['%value%' => 'token'], 'foodomain');

        T::trans($message);

        $parameters = [];

        $transImplode1 = implode(' ', array('trans', '"implode-array"', 'to', 'variable'));
        T::trans($transImplode1, $parameters, $barDomain);

        $transImplode2 = implode(' ', ['trans', '"implode-[]"', 'to', 'variable']);
        T::trans($transImplode2, $parameters, $barDomain);

        $transImplode3 = \implode(' ', array('trans', '"\implode-array"', 'to', 'variable'));
        T::trans($transImplode3, $parameters, $barDomain);

        $transImplode4 = \implode(' ', ['trans', '"\implode-[]"', 'to', 'variable']);
        T::trans($transImplode4, $parameters, $barDomain);

        T::trans(implode(PHP_EOL, array('trans', '"implode-array"')), $parameters, $barDomain);

        T::trans(implode(PHP_EOL, ['trans', '"implode-[]"']), $parameters, $barDomain);

        T::trans(\implode(PHP_EOL, array('trans', '"\implode-array"')), $parameters, $barDomain);

        T::trans(\implode(PHP_EOL, ['trans', '"\implode-[]"']), $parameters, $barDomain);
    }
}

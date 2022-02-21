<?php

namespace Modera\TranslationsBundle\Tests\Unit\TokenExtraction;

use Modera\FoundationBundle\Translation\T;

/**
 * @author Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class FooClass
{
    public function method1()
    {
        T::trans('Broken domain', array(), rand(100));

        T::trans('Default domain', [], null);

        $barDomain = 'bardomain';

        T::trans('Some simple token');

        $message = 'hello ';
        $message .= 'world';

        T::trans('We got something for ya, %s!', array('name' => 'Vassily', 'xxx' => $fn()), 'foodomain');

        T::trans('Another token', [], $barDomain);

        T::trans('Another %value%', ['%value%' => 'token'], 'foodomain');

        T::trans($message);

        $parameters = array();
        $transImplode = implode(' ', array('trans', '"implode"', 'to', 'variable'));

        T::trans($transImplode, $parameters, $barDomain);

        T::trans(implode(PHP_EOL, array('trans', '"implode"')), $parameters, $barDomain);
    }
}

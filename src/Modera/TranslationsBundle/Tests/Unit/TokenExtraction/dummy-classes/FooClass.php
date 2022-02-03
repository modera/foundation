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
        $barDomain = 'bardomain';

        T::trans('Some simple token');

        $message = 'hello ';
        $message .= 'world';

        T::trans('We got something for ya, %s!', array('name' => 'Vassily', 'xxx' => $fn()), 'foodomain');

        T::trans('Another token', null, $barDomain);

        T::trans($message);

        T::trans('Broken translation', null, rand(100));

        $parameters = array();
        $transImplode = implode(' ', array('trans', '"implode"', 'to', 'variable'));

        T::trans($transImplode, $parameters, $barDomain);

        T::trans(implode(PHP_EOL, array('trans', '"implode"')), $parameters, $barDomain);
    }
}

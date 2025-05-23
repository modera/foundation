<?php

namespace Modera\FoundationBundle;

use Modera\FoundationBundle\Translation\T;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @copyright 2013 Modera Foundation
 */
class ModeraFoundationBundle extends Bundle
{
    // override
    public function boot(): void
    {
        $reflClass = new \ReflectionClass(T::class);
        $reflProp = $reflClass->getProperty('container');
        $reflProp->setAccessible(true);
        $reflProp->setValue(null, $this->container);
    }
}

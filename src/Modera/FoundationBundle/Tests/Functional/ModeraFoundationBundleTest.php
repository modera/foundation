<?php

namespace Modera\TranslationsBundle\Tests\Functional;

use Modera\FoundationBundle\Testing\FunctionalTestCase;
use Modera\FoundationBundle\Translation\T;

/**
 * @author Sergei Lissovski <sergei.lissovski@modera.org>
 */
class ModeraFoundationBundleTest extends FunctionalTestCase
{
    public function testBoot()
    {
        $reflProp = new \ReflectionProperty(T::class, 'container');
        $reflProp->setAccessible(true);

        $this->assertInstanceOf(
            'Symfony\Component\DependencyInjection\ContainerInterface',
            $reflProp->getValue()
        );
    }
}

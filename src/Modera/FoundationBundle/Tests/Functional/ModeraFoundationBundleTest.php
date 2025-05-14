<?php

namespace Modera\TranslationsBundle\Tests\Functional;

use Modera\FoundationBundle\Testing\FunctionalTestCase;
use Modera\FoundationBundle\Translation\T;

class ModeraFoundationBundleTest extends FunctionalTestCase
{
    public function testBoot(): void
    {
        $reflProp = new \ReflectionProperty(T::class, 'container');
        $reflProp->setAccessible(true);

        $this->assertInstanceOf(
            'Symfony\Component\DependencyInjection\ContainerInterface',
            $reflProp->getValue(),
        );
    }
}

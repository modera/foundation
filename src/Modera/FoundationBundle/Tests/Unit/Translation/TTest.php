<?php

namespace Modera\FoundationBundle\Tests\Unit\Translation;

use Symfony\Contracts\Translation\TranslatorInterface;
use Modera\FoundationBundle\Translation\T;

class MockTranslator implements TranslatorInterface
{
    public function trans(string $id, array $parameters = [], string $domain = null, string $locale = null): string
    {
        return \json_encode(array($id, $parameters, $domain, $locale));
    }

    public function setLocale($locale)
    {
    }

    public function getLocale()
    {
    }
}

/**
 * @author Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class TTest extends \PHPUnit\Framework\TestCase
{
    private $t;
    private $c;
    private $reflMethod;

    // override
    public function setUp(): void
    {
        $this->t = new MockTranslator();

        $this->c = $this->createMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->c->expects($this->atLeastOnce())
             ->method('get')
             ->with($this->equalTo('translator'))
             ->will($this->returnValue($this->t));

        $reflClass = new \ReflectionClass(T::class);
        $this->reflMethod = $reflClass->getProperty('container');
        $this->reflMethod->setAccessible(true);
        $this->reflMethod->setValue(null, $this->c);
    }

    // override
    public function tearDown(): void
    {
        $this->reflMethod->setValue(null, null);
    }

    public function testTrans()
    {
        $expectedOutput = array(
            'foo id', array('params'), 'foo domain', 'foo locale',
        );

        $this->assertSame(
            \json_encode($expectedOutput),
            T::trans('foo id', array('params'), 'foo domain', 'foo locale')
        );
    }
}

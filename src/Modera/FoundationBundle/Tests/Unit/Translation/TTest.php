<?php

namespace Modera\FoundationBundle\Tests\Unit\Translation;

use Modera\FoundationBundle\Translation\T;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MockTranslator implements TranslatorInterface
{
    public function trans(string $id, array $parameters = [], ?string $domain = null, ?string $locale = null): string
    {
        return \json_encode([$id, $parameters, $domain, $locale]);
    }

    public function setLocale($locale): void
    {
    }

    public function getLocale(): string
    {
        return '';
    }
}

class TTest extends \PHPUnit\Framework\TestCase
{
    private MockTranslator $t;
    private ContainerInterface $c;
    private \ReflectionProperty $reflMethod;

    // override
    public function setUp(): void
    {
        $this->t = new MockTranslator();

        $this->c = $this->createMock(ContainerInterface::class);
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

    public function testTrans(): void
    {
        $expectedOutput = [
            'foo id', ['params'], 'foo domain', 'foo locale',
        ];

        $this->assertSame(
            \json_encode($expectedOutput),
            T::trans('foo id', ['params'], 'foo domain', 'foo locale')
        );
    }
}

<?php

namespace Modera\FileRepositoryBundle\Tests\Unit\Intercepting;

use Modera\FileRepositoryBundle\Authoring\AuthoringInterceptor;
use Modera\FileRepositoryBundle\Entity\Repository;
use Modera\FileRepositoryBundle\Intercepting\DefaultInterceptorsProvider;
use Modera\FileRepositoryBundle\Intercepting\MimeSaverInterceptor;
use Modera\FileRepositoryBundle\Validation\FilePropertiesValidationInterceptor;

class DefaultInterceptorsProviderTest extends \PHPUnit\Framework\TestCase
{
    public function testGetInterceptors(): void
    {
        $dummyFilePropertiesValidationInterceptor = new \stdClass();
        $dummyAuthoringInterceptor = new \stdClass();
        $dummyFooInterceptor = new \stdClass();
        $mimeInterceptor = new \stdClass();

        $container = \Phake::mock('Symfony\Component\DependencyInjection\ContainerInterface');
        \Phake::when($container)
            ->get(FilePropertiesValidationInterceptor::class)
            ->thenReturn($dummyFilePropertiesValidationInterceptor)
        ;
        \Phake::when($container)
            ->get(AuthoringInterceptor::class)
            ->thenReturn($dummyAuthoringInterceptor)
        ;
        \Phake::when($container)
            ->get(MimeSaverInterceptor::class)
            ->thenReturn($mimeInterceptor)
        ;
        \Phake::when($container)
            ->get('foo_interceptor')
            ->thenReturn($dummyFooInterceptor)
        ;

        $repository = \Phake::mock(Repository::class);

        $provider = new DefaultInterceptorsProvider($container);

        $result = $provider->getInterceptors($repository);

        $this->assertEquals(3, \count($result));
        $this->assertSame($dummyFilePropertiesValidationInterceptor, $result[0]);
        $this->assertSame($mimeInterceptor, $result[1]);
        $this->assertSame($dummyAuthoringInterceptor, $result[2]);

        // and now with a "interceptors" config:

        \Phake::when($repository)
            ->getConfig()
            ->thenReturn(['interceptors' => ['foo_interceptor']])
        ;

        $result = $provider->getInterceptors($repository);

        $this->assertEquals(4, \count($result));
        $this->assertSame($dummyFilePropertiesValidationInterceptor, $result[0]);
        $this->assertSame($mimeInterceptor, $result[1]);
        $this->assertSame($dummyAuthoringInterceptor, $result[2]);
        $this->assertSame($dummyFooInterceptor, $result[3]);
    }
}

<?php

namespace Modera\MJRCacheAwareClassLoaderBundle\Tests\Unit\VersionResolving;

use Modera\MJRCacheAwareClassLoaderBundle\DependencyInjection\ModeraMJRCacheAwareClassLoaderExtension;
use Modera\MJRCacheAwareClassLoaderBundle\VersionResolving\StandardVersionResolver;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class StandardVersionResolverTest extends \PHPUnit\Framework\TestCase
{
    private ContainerInterface $container;

    // override
    public function setUp(): void
    {
        $this->container = \Phake::mock(ContainerInterface::class);
    }

    // override
    public function tearDown(): void
    {
        @\unlink(__DIR__.'/../modera-version.txt');
    }

    public function testResolveWithSemanticConfig(): void
    {
        $config = [
            'version' => 'foo-bar',
        ];

        $kernel = \Phake::mock(KernelInterface::class);
        \Phake::when($this->container)->get('kernel')->thenReturn($kernel);
        \Phake::when($this->container)->getParameter(ModeraMJRCacheAwareClassLoaderExtension::CONFIG_KEY)->thenReturn($config);

        $resolver = new StandardVersionResolver($this->container);

        $this->assertEquals('foo-bar', $resolver->resolve());
    }

    public function testResolveWithFile(): void
    {
        \file_put_contents(__DIR__.'/../modera-version.txt', 'ololo');

        $kernel = \Phake::mock(KernelInterface::class);
        \Phake::when($kernel)->getProjectDir()->thenReturn(\dirname(__DIR__));
        \Phake::when($this->container)->get('kernel')->thenReturn($kernel);
        \Phake::when($this->container)->getParameter(ModeraMJRCacheAwareClassLoaderExtension::CONFIG_KEY)->thenReturn([]);

        $resolver = new StandardVersionResolver($this->container);

        $this->assertEquals('ololo', $resolver->resolve());
    }

    public function testResolve(): void
    {
        $kernel = \Phake::mock(KernelInterface::class);
        \Phake::when($kernel)->getProjectDir()->thenReturn(\dirname(__DIR__));
        \Phake::when($this->container)->get('kernel')->thenReturn($kernel);
        \Phake::when($this->container)->getParameter(ModeraMJRCacheAwareClassLoaderExtension::CONFIG_KEY)->thenReturn([]);

        $resolver = new StandardVersionResolver($this->container);

        $this->assertEquals('1.0.0', $resolver->resolve());
    }
}

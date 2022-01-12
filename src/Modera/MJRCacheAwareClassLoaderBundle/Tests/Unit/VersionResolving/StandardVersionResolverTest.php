<?php

namespace Modera\MJRCacheAwareClassLoaderBundle\Tests\Unit\VersionResolving;

use Symfony\Component\HttpKernel\KernelInterface;
use Modera\MJRCacheAwareClassLoaderBundle\DependencyInjection\ModeraMJRCacheAwareClassLoaderExtension;
use Modera\MJRCacheAwareClassLoaderBundle\VersionResolving\StandardVersionResolver;

// TODO: remove in v5.x
interface MockKernelInterface extends KernelInterface
{
    public function getProjectDir();
}

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class StandardVersionResolverTest extends \PHPUnit\Framework\TestCase
{
    private $container;

    // override
    public function setUp(): void
    {
        $this->container = \Phake::mock('Symfony\Component\DependencyInjection\ContainerInterface');
    }

    // override
    public function tearDown(): void
    {
        @unlink(__DIR__.'/../modera-version.txt');
    }

    public function testResolveWithSemanticConfig()
    {
        $config = array(
            'version' => 'foo-bar',
        );

        $kernel = \Phake::mock(MockKernelInterface::class);
        \Phake::when($this->container)->get('kernel')->thenReturn($kernel);
        \Phake::when($this->container)->getParameter(ModeraMJRCacheAwareClassLoaderExtension::CONFIG_KEY)->thenReturn($config);

        $resolver = new StandardVersionResolver($this->container);

        $this->assertEquals('foo-bar', $resolver->resolve());
    }

    public function testResolveWithFile()
    {
        file_put_contents(__DIR__.'/../modera-version.txt', 'ololo');

        $kernel = \Phake::mock(MockKernelInterface::class);
        \Phake::when($kernel)->getProjectDir()->thenReturn(\dirname(__DIR__));
        \Phake::when($this->container)->get('kernel')->thenReturn($kernel);
        \Phake::when($this->container)->getParameter(ModeraMJRCacheAwareClassLoaderExtension::CONFIG_KEY)->thenReturn(array());

        $resolver = new StandardVersionResolver($this->container);

        $this->assertEquals('ololo', $resolver->resolve());
    }

    public function testResolve()
    {
        $kernel = \Phake::mock(MockKernelInterface::class);
        \Phake::when($kernel)->getProjectDir()->thenReturn(\dirname(__DIR__));
        \Phake::when($this->container)->get('kernel')->thenReturn($kernel);
        \Phake::when($this->container)->getParameter(ModeraMJRCacheAwareClassLoaderExtension::CONFIG_KEY)->thenReturn(array());

        $resolver = new StandardVersionResolver($this->container);

        $this->assertEquals('1.0.0', $resolver->resolve());
    }
}

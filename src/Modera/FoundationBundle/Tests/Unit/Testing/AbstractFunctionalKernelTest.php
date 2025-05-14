<?php

namespace Modera\FoundationBundle\Tests\Unit\Testing;

use Symfony\Component\Config\Loader\LoaderInterface;

require_once __DIR__.'/../../Fixtures/App/app/ModeraFoundationAppKernel.php';

class AbstractFunctionalKernelTest extends \PHPUnit\Framework\TestCase
{
    private \ModeraFoundationAppKernel $kernel;

    protected function setUp(): void
    {
        $this->kernel = new \ModeraFoundationAppKernel('test', true);
    }

    public function testRegisterContainerConfiguration(): void
    {
        $loader = \Phake::mock(LoaderInterface::class);

        $this->kernel->registerContainerConfiguration($loader);

        \Phake::verify($loader)->load($this->stringContains('src/Modera/FoundationBundle/Tests/Fixtures/App/app/config/config.yaml'));
    }

    public function testGetCacheDir(): void
    {
        $this->assertMatchesRegularExpression('/ModeraFoundation/', $this->kernel->getCacheDir());
    }

    public function testGetLogDir(): void
    {
        $this->assertMatchesRegularExpression('/ModeraFoundation/', $this->kernel->getLogDir());
    }

    public function testGetContainerClass(): void
    {
        $this->kernel->boot();
        $this->assertMatchesRegularExpression('/ModeraFoundation/', $this->kernel->getContainer()->getParameter('kernel.container_class'));
    }
}

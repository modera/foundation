<?php

namespace Modera\FoundationBundle\Tests\Unit\Testing;

use Symfony\Component\Config\Loader\LoaderInterface;

require_once __DIR__.'/../../Fixtures/App/app/ModeraFoundationAppKernel.php';

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
class AbstractFunctionalKernelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \ModeraFoundationAppKernel
     */
    private $kernel;

    protected function setUp()
    {
        $this->kernel = new \ModeraFoundationAppKernel('test', true);
    }

    public function testRegisterContainerConfiguration()
    {
        $loader = \Phake::mock(LoaderInterface::class);

        $this->kernel->registerContainerConfiguration($loader);

        \Phake::verify($loader)->load($this->stringContains('src/Modera/FoundationBundle/Tests/Fixtures/App/app/config/config.yml'));
    }

    public function testGetCacheDir()
    {
        $this->assertContains('ModeraFoundation', $this->kernel->getCacheDir());
    }

    public function testGetLogDir()
    {
        $this->assertContains('ModeraFoundation', $this->kernel->getLogDir());
    }

    public function testGetContainerClass()
    {
        $this->kernel->boot();
        $this->assertContains('ModeraFoundation', $this->kernel->getContainer()->getParameter('kernel.container_class'));
    }
}
<?php

namespace Modera\DynamicallyConfigurableAppBundle\Tests\Unit\ValueHandling;

use Guzzle\Common\Exception\RuntimeException;
use Modera\ConfigBundle\Config\ConfigurationEntryInterface;
use Modera\DynamicallyConfigurableAppBundle\Tests\Fixtures\DummyKernel;
use Modera\DynamicallyConfigurableAppBundle\ValueHandling\KernelConfigWriter;
use Modera\DynamicallyConfigurableAppBundle\ModeraDynamicallyConfigurableAppBundle as Bundle;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2017 Modera Foundation
 */
class KernelConfigWriterTest extends \PHPUnit_Framework_TestCase
{
    private static $kernelJsonPathname;

    public function setUp()
    {
        if (!self::$kernelJsonPathname) {
            self::$kernelJsonPathname = dirname((new \ReflectionClass(DummyKernel::class))->getFileName()).'/kernel.json';
        }

        file_put_contents(self::$kernelJsonPathname, json_encode(array()));
    }

    public function tearDown()
    {
        unlink(self::$kernelJsonPathname);
    }

    /**
     * @dataProvider happyPathDataProvider
     */
    public function testHappyPath($name, $value, $keyToCheck)
    {
        $expectedValue = $value;

        $entry = $this->createMockEntry($name, $value);

        $writer = new KernelConfigWriter(DummyKernel::class);
        $writer->onUpdate($entry);

        $result = json_decode(file_get_contents(self::$kernelJsonPathname), true);

        $this->assertTrue(is_array($result));
        $this->assertArrayHasKey('_comment', $result);
        $this->assertArrayHasKey('debug', $result);
        $this->assertArrayHasKey('env', $result);
        $this->assertEquals($expectedValue, $result[$keyToCheck]);
    }

    public function happyPathDataProvider()
    {
        return [
            'enable kernel debug' => [Bundle::CONFIG_KERNEL_DEBUG, true, 'debug'],
            'disable kernel debug' => [Bundle::CONFIG_KERNEL_DEBUG, false, 'debug'],
            'set env to "prod"' => [Bundle::CONFIG_KERNEL_ENV, 'prod', 'env'],
            'set env to "dev"' => [Bundle::CONFIG_KERNEL_ENV, 'dev', 'env'],
        ];
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessageRegExp /Unable to find kernel.json, looked in .+/
     */
    public function testWhenKernelJsonNotFound()
    {
        $entry = $this->createMockEntry(Bundle::CONFIG_KERNEL_DEBUG, true);

        // Current directory doesn't have "kernel.json" and that's exactly what we need
        $writer = new KernelConfigWriter(__CLASS__);
        $writer->onUpdate($entry);
    }

    private function createMockEntry($name, $value)
    {
        $entry = \Phake::mock(ConfigurationEntryInterface::class);
        \Phake::when($entry)
            ->getName()
            ->thenReturn($name)
        ;
        \Phake::when($entry)
            ->getValue()
            ->thenReturn($value)
        ;

        return $entry;
    }
}
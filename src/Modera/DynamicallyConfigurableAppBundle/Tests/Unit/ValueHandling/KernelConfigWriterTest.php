<?php

namespace Modera\DynamicallyConfigurableAppBundle\Tests\Unit\ValueHandling;

use Modera\ConfigBundle\Config\ConfigurationEntryInterface;
use Modera\DynamicallyConfigurableAppBundle\KernelConfigInterface;
use Modera\DynamicallyConfigurableAppBundle\ValueHandling\KernelConfigWriter;
use Modera\DynamicallyConfigurableAppBundle\ModeraDynamicallyConfigurableAppBundle as Bundle;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2018 Modera Foundation
 */
class KernelConfigWriterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider onUpdateDataProvider
     */
    public function testOnUpdate($name, $value, $keyToCheck)
    {
        $expectedValue = $value;

        $entry = $this->createMockEntry($name, $value);

        $writer = new KernelConfigWriter(DummyKernelConfig::class);
        $writer->onUpdate($entry);

        $result = DummyKernelConfig::read();

        $this->assertTrue(is_array($result));
        $this->assertArrayHasKey('debug', $result);
        $this->assertArrayHasKey('env', $result);
        $this->assertEquals($expectedValue, $result[$keyToCheck]);
    }

    /**
     * @return array
     */
    public function onUpdateDataProvider()
    {
        return [
            'set env to "dev"'     => [Bundle::CONFIG_KERNEL_ENV, 'dev', 'env'],
            'enable kernel debug'  => [Bundle::CONFIG_KERNEL_DEBUG, true, 'debug'],
            'set env to "prod"'    => [Bundle::CONFIG_KERNEL_ENV, 'prod', 'env'],
            'disable kernel debug' => [Bundle::CONFIG_KERNEL_DEBUG, false, 'debug'],
        ];
    }

    /**
     * @param string $name
     * @param string $value
     * @return mixed
     */
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

class DummyKernelConfig implements KernelConfigInterface
{
    /**
     * @var array
     */
    private static $mode = array(
        'env'   => 'prod',
        'debug' => false,
    );

    /**
     * {@inheritdoc}
     */
    public static function write(array $mode)
    {
        static::$mode = array_merge(static::read(), $mode);
    }

    /**
     * {@inheritdoc}
     */
    public static function read(): array
    {
        return static::$mode;
    }
}

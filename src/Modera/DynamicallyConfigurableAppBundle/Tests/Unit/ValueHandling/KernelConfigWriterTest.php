<?php

namespace Modera\DynamicallyConfigurableAppBundle\Tests\Unit\ValueHandling;

use Modera\ConfigBundle\Config\ConfigurationEntryInterface;
use Modera\DynamicallyConfigurableAppBundle\KernelConfigInterface;
use Modera\DynamicallyConfigurableAppBundle\ModeraDynamicallyConfigurableAppBundle as Bundle;
use Modera\DynamicallyConfigurableAppBundle\ValueHandling\KernelConfigWriter;

class KernelConfigWriterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider onUpdateDataProvider
     */
    public function testOnUpdate($name, $value, $keyToCheck): void
    {
        $expectedValue = $value;

        $entry = $this->createMockEntry($name, $value);

        $writer = new KernelConfigWriter(DummyKernelConfig::class);
        $writer->onUpdate($entry);

        $result = DummyKernelConfig::read();

        $this->assertTrue(\is_array($result));
        $this->assertArrayHasKey('debug', $result);
        $this->assertArrayHasKey('env', $result);
        $this->assertEquals($expectedValue, $result[$keyToCheck]);
    }

    public static function onUpdateDataProvider(): array
    {
        return [
            'set env to "dev"' => [Bundle::CONFIG_KERNEL_ENV, 'dev', 'env'],
            'enable kernel debug' => [Bundle::CONFIG_KERNEL_DEBUG, true, 'debug'],
            'set env to "prod"' => [Bundle::CONFIG_KERNEL_ENV, 'prod', 'env'],
            'disable kernel debug' => [Bundle::CONFIG_KERNEL_DEBUG, false, 'debug'],
        ];
    }

    private function createMockEntry(string $name, string $value): object
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
    private static array $mode = [
        'env' => 'prod',
        'debug' => false,
    ];

    public static function write(array $mode): void
    {
        static::$mode = \array_merge(static::read(), $mode);
    }

    public static function read(): array
    {
        return static::$mode;
    }
}

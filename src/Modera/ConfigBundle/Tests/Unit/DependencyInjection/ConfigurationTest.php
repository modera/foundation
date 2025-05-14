<?php

namespace Modera\ConfigBundle\Tests\Unit\DependencyInjection;

use Modera\ConfigBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends \PHPUnit\Framework\TestCase
{
    public function testNoExplicitConfigProvided(): void
    {
        $configuration = new Configuration();

        $processor = new Processor();
        $config = $processor->processConfiguration($configuration, []);

        $this->assertArrayHasKey('owner_entity', $config);
        $this->assertNull($config['owner_entity']);
    }

    public function testWithConfigGiven(): void
    {
        $configuration = new Configuration();

        $processor = new Processor();
        $config = $processor->processConfiguration($configuration, [
            'modera_config' => [
                'owner_entity' => 'FooEntity',
            ],
        ]);

        $this->assertArrayHasKey('owner_entity', $config);
        $this->assertEquals('FooEntity', $config['owner_entity']);
    }
}

<?php

namespace Modera\BackendConfigUtilsBundle\Tests\Unit\Controller;

use Modera\BackendConfigUtilsBundle\Controller\DefaultController;
use Modera\ConfigBundle\Entity\ConfigurationEntry;
use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\ExpanderBundle\Ext\ExtensionPoint;
use Modera\ExpanderBundle\Ext\ExtensionPointManager;
use Modera\ExpanderBundle\Ext\ExtensionProvider;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DefaultControllerTest extends \PHPUnit\Framework\TestCase
{
    private DefaultController $c;

    public function setUp(): void
    {
        $provider = \Phake::mock(ContributorInterface::class);
        \Phake::when($provider)
            ->getItems()
            ->thenReturn([])
        ;

        $container = \Phake::mock(ContainerInterface::class);
        \Phake::when($container)
            ->has('modera_config.config_entries_provider')
            ->thenReturn(true)
        ;
        \Phake::when($container)
            ->get('modera_config.config_entries_provider')
            ->thenReturn($provider)
        ;

        $extensionPointManager = \Phake::mock(ExtensionPointManager::class);
        \Phake::when($extensionPointManager)
            ->has('modera_config.config_entries')
            ->thenReturn(true)
        ;
        \Phake::when($extensionPointManager)
            ->get('modera_config.config_entries')
            ->thenReturn(new ExtensionPoint('modera_config.config_entries'))
        ;

        $this->c = new DefaultController(new ExtensionProvider($container, $extensionPointManager));
    }

    public function testGetConfigHydration(): void
    {
        $config = $this->c->getConfig();

        $this->assertTrue(\is_array($config));
        $this->assertTrue(isset($config['hydration']['groups']['list']));

        $hydrator = $config['hydration']['groups']['list'];

        $this->assertTrue(is_callable($hydrator));

        $entry = \Phake::mock(ConfigurationEntry::class);

        $this->teachEntry($entry, 'getId', 0);
        $this->teachEntry($entry, 'getName', 'foo_name');
        $this->teachEntry($entry, 'getReadableName', 'foo_rn');
        $this->teachEntry($entry, 'getReadableValue', 'foo_rv');
        $this->teachEntry($entry, 'getValue', 'foo_v');
        $this->teachEntry($entry, 'isReadOnly', true);
        $this->teachEntry($entry, 'getClientHandlerConfig', ['foo_ch']);

        $result = $hydrator($entry);

        $this->assertTrue(\is_array($result));
        foreach (['id', 'name', 'readableName', 'readableValue', 'value', 'isReadOnly', 'editorConfig'] as $key) {
            $this->assertArrayHasKey($key, $result);
        }

        $this->assertEquals(0, $result['id']);
        $this->assertEquals('foo_name', $result['name']);
        $this->assertEquals('foo_rn', $result['readableName']);
        $this->assertEquals('foo_rv', $result['readableValue']);
        $this->assertEquals(true, $result['isReadOnly']);
        $this->assertEquals(['foo_ch'], $result['editorConfig']);
    }

    public function testGetConfigMapDataOnUpdate(): void
    {
        $config = $this->c->getConfig();

        $this->assertTrue(\is_array($config));
        $this->assertArrayHasKey('map_data_on_update', $config);
        $this->assertTrue(\is_callable($config['map_data_on_update']));

        $entry = \Phake::mock(ConfigurationEntry::class);
        $this->teachEntry($entry, 'isReadOnly', true);
    }

    private function teachEntry($mock, $methodName, $returnValue): void
    {
        \Phake::when($mock)
            ->$methodName()
            ->thenReturn($returnValue)
        ;
    }
}

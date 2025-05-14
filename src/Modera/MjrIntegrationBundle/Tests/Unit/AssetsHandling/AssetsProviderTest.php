<?php

namespace Modera\MjrIntegrationBundle\Tests\Unit\AssetsHandling;

use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\ExpanderBundle\Ext\ExtensionPoint;
use Modera\ExpanderBundle\Ext\ExtensionPointManager;
use Modera\ExpanderBundle\Ext\ExtensionProvider;
use Modera\MjrIntegrationBundle\AssetsHandling\AssetsProvider;

class AssetsProviderTest extends \PHPUnit\Framework\TestCase
{
    private function createMockProvider(array $assets = []): ContributorInterface
    {
        $mock = \Phake::mock(ContributorInterface::class);
        \Phake::when($mock)->getItems()->thenReturn($assets);

        return $mock;
    }

    private function createIUT(array $cssAssets = [], array $jsAssets = []): AssetsProvider
    {
        $container = \Phake::mock('Symfony\Component\DependencyInjection\ContainerInterface');
        \Phake::when($container)
            ->get('modera_mjr_integration.css_resources_provider')
            ->thenReturn($this->createMockProvider($cssAssets))
        ;
        \Phake::when($container)
            ->get('modera_mjr_integration.js_resources_provider')
            ->thenReturn($this->createMockProvider($jsAssets))
        ;

        $extensionPointManager = \Phake::mock(ExtensionPointManager::class);
        \Phake::when($extensionPointManager)
            ->has('modera_mjr_integration.css_resources')
            ->thenReturn(true)
        ;
        \Phake::when($extensionPointManager)
            ->get('modera_mjr_integration.css_resources')
            ->thenReturn(new ExtensionPoint('modera_mjr_integration.css_resources'))
        ;
        \Phake::when($extensionPointManager)
            ->has('modera_mjr_integration.js_resources')
            ->thenReturn(true)
        ;
        \Phake::when($extensionPointManager)
            ->get('modera_mjr_integration.js_resources')
            ->thenReturn(new ExtensionPoint('modera_mjr_integration.js_resources'))
        ;

        return new AssetsProvider(new ExtensionProvider($container, $extensionPointManager));
    }

    public function testGetCssAssets(): void
    {
        $resources = ['blocking.css', '!yo-blocker.css', 'blocking3.css', '*non-blocking.css', 'non*blockingwannabe.css'];

        $provider = $this->createIUT($resources);

        $blocking = $provider->getCssAssets(AssetsProvider::TYPE_BLOCKING);

        $this->assertEquals(4, \count($blocking));
        $this->assertEquals($resources[0], $blocking[0]);
        $this->assertEquals(\substr($resources[1], 1), $blocking[1]);
        $this->assertEquals($resources[4], $blocking[3]);

        $nonBlocking = $provider->getCssAssets(AssetsProvider::TYPE_NON_BLOCKING);

        $this->assertEquals(1, \count($nonBlocking));

        $this->assertEquals(\substr($resources[3], 1), $nonBlocking[0], 'Returned filename must not contain * suffix');
    }

    public function testGetJsAssets(): void
    {
        $resources = ['blocking.js', '!yo-blocker.js', 'blocking3.js', '*non-blocking.js', 'non*blockingwannabe.js'];

        $provider = $this->createIUT($resources);

        $blocking = $provider->getCssAssets(AssetsProvider::TYPE_BLOCKING);

        $this->assertEquals(4, \count($blocking));
        $this->assertEquals($resources[0], $blocking[0]);
        $this->assertEquals(\substr($resources[1], 1), $blocking[1]);
        $this->assertEquals($resources[4], $blocking[3]);

        $nonBlocking = $provider->getCssAssets(AssetsProvider::TYPE_NON_BLOCKING);

        $this->assertEquals(1, \count($nonBlocking));

        $this->assertEquals(\substr($resources[3], 1), $nonBlocking[0], 'Returned filename must not contain * suffix');
    }

    public function testGetCssAssetsWithInvalidTypeGiven(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $provider = $this->createIUT();

        $provider->getCssAssets('foo');
    }

    public function testGetJsAssetsWithInvalidTypeGiven(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $provider = $this->createIUT();

        $provider->getJavascriptAssets('foo');
    }
}

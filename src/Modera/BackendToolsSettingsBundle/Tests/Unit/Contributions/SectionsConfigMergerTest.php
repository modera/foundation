<?php

namespace Modera\BackendToolsSettingsBundle\Tests\Unit\Contributions;

use Modera\BackendToolsSettingsBundle\Contributions\SectionsConfigMerger;
use Modera\BackendToolsSettingsBundle\Section\SectionInterface;
use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\ExpanderBundle\Ext\ExtensionPoint;
use Modera\ExpanderBundle\Ext\ExtensionPointManager;
use Modera\ExpanderBundle\Ext\ExtensionProvider;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DummySection implements SectionInterface
{
    public function getId(): string
    {
        return 'foo-id';
    }

    public function getName(): string
    {
        return 'foo-id';
    }

    public function getGlyph(): ?string
    {
        return 'foo-glyph';
    }

    public function getActivityClass(): string
    {
        return 'foo-ac';
    }

    public function getMeta(): array
    {
        return [
            'megameta',
        ];
    }
}

class SectionsConfigMergerTest extends \PHPUnit\Framework\TestCase
{
    public function testMerge(): void
    {
        $ds = new DummySection();

        $sectionsProvider = $this->createMock(ContributorInterface::class);
        $sectionsProvider->expects($this->atLeastOnce())
                         ->method('getItems')
                         ->will($this->returnValue([$ds]));

        $container = \Phake::mock(ContainerInterface::class);
        \Phake::when($container)
            ->has('modera_backend_tools_settings.contributions.sections_provider')
            ->thenReturn(true)
        ;
        \Phake::when($container)
            ->get('modera_backend_tools_settings.contributions.sections_provider')
            ->thenReturn($sectionsProvider)
        ;

        $extensionPointManager = \Phake::mock(ExtensionPointManager::class);
        \Phake::when($extensionPointManager)
            ->has('modera_backend_tools_settings.contributions.sections')
            ->thenReturn(true)
        ;
        \Phake::when($extensionPointManager)
            ->get('modera_backend_tools_settings.contributions.sections')
            ->thenReturn(new ExtensionPoint('modera_backend_tools_settings.contributions.sections'))
        ;

        $configMerger = new SectionsConfigMerger(new ExtensionProvider($container, $extensionPointManager));

        $existingConfig = [
            'someKey' => 'someValue',
        ];

        $result = $configMerger->merge($existingConfig);

        $this->assertTrue(\is_array($result));
        $this->assertArrayHasKey('someKey', $result);
        $this->assertEquals('someValue', $result['someKey']);

        $this->assertArrayHasKey('settingsSections', $result);
        $this->assertTrue(is_array($result['settingsSections']));
        $this->assertEquals(1, \count($result['settingsSections']));

        $section = $result['settingsSections'][0];

        $this->assertTrue(is_array($section));
        $this->assertArrayHasKey('id', $section);
        $this->assertEquals($ds->getId(), $section['id']);
        $this->assertArrayHasKey('name', $section);
        $this->assertEquals($ds->getName(), $section['name']);
        $this->assertArrayHasKey('activityClass', $section);
        $this->assertEquals($ds->getActivityClass(), $section['activityClass']);
        $this->assertArrayHasKey('glyph', $section);
        $this->assertEquals($ds->getGlyph(), $section['glyph']);
        $this->assertArrayHasKey('meta', $section);
        $this->assertEquals($ds->getMeta(), $section['meta']);
    }
}

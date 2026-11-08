<?php

namespace Modera\BackendToolsSettingsBundle\Tests\Unit\Contributions;

use Modera\BackendToolsSettingsBundle\Contributions\SectionsConfigMerger;
use Modera\BackendToolsSettingsBundle\Section\GroupSection;
use Modera\BackendToolsSettingsBundle\Section\SectionInterface;
use Modera\BackendToolsSettingsBundle\Section\StandardSection;
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
        $this->assertNull($section['parent']);
        $this->assertFalse($section['group']);
    }

    public function testChildrenFollowTheirParent(): void
    {
        $result = $this->mergeSections([
            new StandardSection('vat', 'VAT', 'Vat.Activity', null, [], 'pricing'),
            new StandardSection('users', 'Users', 'Users.Activity'),
            new GroupSection('pricing', 'Pricing'),
            new StandardSection('margins', 'Margins', 'Margins.Activity', null, [], 'pricing'),
        ]);

        $this->assertSame(['users', 'pricing', 'vat', 'margins'], \array_column($result, 'id'));
        $this->assertSame([null, null, 'pricing', 'pricing'], \array_column($result, 'parent'));
    }

    public function testGroupSectionHasNoActivity(): void
    {
        $result = $this->mergeSections([new GroupSection('pricing', 'Pricing', 'tags')]);

        $this->assertTrue($result[0]['group']);
        $this->assertSame('', $result[0]['activityClass']);
        $this->assertSame('tags', $result[0]['glyph']);
    }

    public function testOrphanIsShownAtTopLevel(): void
    {
        $result = $this->mergeSections([
            new StandardSection('vat', 'VAT', 'Vat.Activity', null, [], 'missing'),
        ]);

        $this->assertSame('vat', $result[0]['id']);
        $this->assertNull($result[0]['parent']);
    }

    public function testGrandchildIsShownAtTopLevel(): void
    {
        $result = $this->mergeSections([
            new GroupSection('pricing', 'Pricing'),
            new StandardSection('vat', 'VAT', 'Vat.Activity', null, [], 'pricing'),
            new StandardSection('rates', 'Rates', 'Rates.Activity', null, [], 'vat'),
        ]);

        $this->assertSame(['pricing', 'vat', 'rates'], \array_column($result, 'id'));
        $this->assertSame([null, 'pricing', null], \array_column($result, 'parent'));
    }

    public function testParentLoopIsShownAtTopLevel(): void
    {
        $result = $this->mergeSections([
            new StandardSection('a', 'A', 'A.Activity', null, [], 'b'),
            new StandardSection('b', 'B', 'B.Activity', null, [], 'a'),
        ]);

        $this->assertSame(['a', 'b'], \array_column($result, 'id'));
        $this->assertSame([null, null], \array_column($result, 'parent'));
    }

    /**
     * @param SectionInterface[] $sections
     *
     * @return array<int, array<string, mixed>>
     */
    private function mergeSections(array $sections): array
    {
        $contributor = $this->createMock(ContributorInterface::class);
        $contributor->method('getItems')->willReturn($sections);

        $extensionProvider = $this->createMock(ExtensionProvider::class);
        $extensionProvider->method('get')->willReturn($contributor);

        return (new SectionsConfigMerger($extensionProvider))->merge([])['settingsSections'];
    }
}

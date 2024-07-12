<?php

namespace Modera\BackendToolsSettingsBundle\Tests\Unit\Contributions;

use Modera\BackendToolsSettingsBundle\Contributions\SectionsConfigMerger;
use Modera\BackendToolsSettingsBundle\Section\SectionInterface;
use Modera\ExpanderBundle\Ext\ContributorInterface;

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
        return array(
            'megameta',
        );
    }
}

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class SectionsConfigMergerTest extends \PHPUnit\Framework\TestCase
{
    public function testMerge()
    {
        $ds = new DummySection();

        $sectionsProvider = $this->createMock(ContributorInterface::CLAZZ);
        $sectionsProvider->expects($this->atLeastOnce())
                         ->method('getItems')
                         ->will($this->returnValue(array($ds)));

        $configMerger = new SectionsConfigMerger($sectionsProvider);

        $existingConfig = array(
            'someKey' => 'someValue',
        );

        $result = $configMerger->merge($existingConfig);

        $this->assertTrue(is_array($result));
        $this->assertArrayHasKey('someKey', $result);
        $this->assertEquals('someValue', $result['someKey']);

        $this->assertArrayHasKey('settingsSections', $result);
        $this->assertTrue(is_array($result['settingsSections']));
        $this->assertEquals(1, count($result['settingsSections']));

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

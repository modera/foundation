<?php

namespace Modera\MjrIntegrationBundle\Tests\Unit\Help;

use Modera\MjrIntegrationBundle\Help\SimpleHelpMenuItem;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
class SimpleHelpMenuItemTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateActivityAware()
    {
        $item = SimpleHelpMenuItem::createActivityAware('foo-label', 'foo-activity', ['bar']);

        $this->assertInstanceOf(SimpleHelpMenuItem::class, $item);
        $this->assertEquals('foo-label', $item->getLabel());
        $this->assertEquals('foo-activity', $item->getActivityId());
        $this->assertEquals(['bar'], $item->getActivityParams());
        $this->assertNull($item->getIntentId());
        $this->assertEquals([], $item->getIntentParams());
        $this->assertNull($item->getUrl());
    }

    public function testCreateIntentAware()
    {
        $item = SimpleHelpMenuItem::createIntentAware('foo-label', 'foo-intent', ['bar']);

        $this->assertInstanceOf(SimpleHelpMenuItem::class, $item);
        $this->assertEquals('foo-label', $item->getLabel());
        $this->assertEquals('foo-intent', $item->getIntentId());
        $this->assertEquals(['bar'], $item->getIntentParams());
        $this->assertNull($item->getActivityId());
        $this->assertEquals([], $item->getActivityParams());
        $this->assertNull($item->getUrl());
    }

    public function testCreateUrlAware()
    {
        $item = SimpleHelpMenuItem::createUrlAware('foo-label', 'foo-url');

        $this->assertInstanceOf(SimpleHelpMenuItem::class, $item);
        $this->assertEquals('foo-label', $item->getLabel());
        $this->assertEquals('foo-url', $item->getUrl());
        $this->assertNull($item->getActivityId());
        $this->assertEquals([], $item->getActivityParams());
        $this->assertNull($item->getIntentId());
        $this->assertEquals([], $item->getIntentParams());
    }
}

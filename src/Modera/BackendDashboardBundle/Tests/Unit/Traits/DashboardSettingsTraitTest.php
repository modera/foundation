<?php

namespace Modera\BackendDashboardBundle\Tests\Unit\Unit\Traits;

use Modera\BackendDashboardBundle\Traits\DashboardSettingsTrait;

class DummyEntity
{
    use DashboardSettingsTrait;

    /**
     * @var array
     */
    public $dashboardSettings = array();

    /**
     * {@inheritdoc}
     */
    public function getDashboardSettings()
    {
        return $this->dashboardSettings;
    }
}

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
class DashboardSettingsTraitTest extends \PHPUnit_Framework_TestCase
{
    public function testHasAccessToDashboard()
    {
        $entity = new DummyEntity();

        $this->assertFalse($entity->hasAccessToDashboard('foo'));

        $entity->dashboardSettings['hasAccess'] = ['foo'];

        $this->assertTrue($entity->hasAccessToDashboard('foo'));
    }

    public function testGetDefaultDashboardId()
    {
        $entity = new DummyEntity();

        $this->assertNull($entity->getDefaultDashboardId());

        $entity->dashboardSettings['defaultDashboard'] = 'foo';

        $this->assertEquals('foo', $entity->getDefaultDashboardId());
    }
}

<?php

namespace Modera\DynamicallyConfigurableMJRBundle\Tests\Unit\Contributions;

use Modera\BackendToolsSettingsBundle\Section\StandardSection;
use Modera\DynamicallyConfigurableMJRBundle\Contributions\SettingsSectionsProvider;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Modera\BackendConfigUtilsBundle\ModeraBackendConfigUtilsBundle;
use Modera\MJRSecurityIntegrationBundle\ModeraMJRSecurityIntegrationBundle;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
class SettingsSectionsProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testGetItems()
    {
        $authorizationChecker = \Phake::mock(AuthorizationCheckerInterface::class);
        \Phake::when($authorizationChecker)
            ->isGranted(ModeraBackendConfigUtilsBundle::ROLE_ACCESS_BACKEND_SYSTEM_SETTINGS, $this->anything())
            ->thenReturn(true);

        $provider = new SettingsSectionsProvider($authorizationChecker);

        $items = $provider->getItems();

        $this->assertTrue(is_array($items));
        $this->assertEquals(1, count($items));

        /* @var StandardSection $section */
        $section = $items[0];
        $this->assertInstanceOf('Modera\BackendToolsSettingsBundle\Section\StandardSection', $items[0]);
        $this->assertEquals('general', $section->getId());
        $this->assertEquals('General', $section->getName());
        $this->assertEquals('Modera.backend.dcmjr.runtime.GeneralSiteSettingsActivity', $section->getActivityClass());

        $expectedMeta = array(
            'activationParams' => array(
                'category' => 'general',
            ),
        );
        $this->assertEquals($expectedMeta, $section->getMeta());
    }

    public function testGetItemsWithoutAccess()
    {
        $authorizationChecker = \Phake::mock(AuthorizationCheckerInterface::class);
        \Phake::when($authorizationChecker)
            ->isGranted(ModeraMJRSecurityIntegrationBundle::ROLE_BACKEND_USER, $this->anything())
            ->thenReturn(false);

        $provider = new SettingsSectionsProvider($authorizationChecker);

        $items = $provider->getItems();

        $this->assertTrue(is_array($items));
        $this->assertEquals(0, count($items));
    }
}

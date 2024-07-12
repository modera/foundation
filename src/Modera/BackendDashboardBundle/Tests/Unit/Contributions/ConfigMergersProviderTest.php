<?php

namespace Modera\BackendDashboardBundle\Tests\Unit\Contributions;

use Modera\BackendDashboardBundle\Contributions\ConfigMergersProvider;
use Modera\BackendDashboardBundle\Dashboard\DashboardInterface;
use Modera\BackendDashboardBundle\Service\DashboardManager;
use Modera\SecurityBundle\Entity\User;
use Modera\ExpanderBundle\Ext\ContributorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @author    Alex Rudakov <alexandr.rudakov@modera.org>
 * @copyright 2014 Modera Foundation
 */
class ConfigMergersProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ConfigMergersProvider
     */
    private $provider;

    private $container;

    private $contributor;

    private $tokenStorage;

    private $dashboardMgr;

    protected function setUp(): void
    {
        $this->container = \Phake::mock(ContainerInterface::class);
        $this->contributor = \Phake::mock(ContributorInterface::class);
        $this->tokenStorage = \Phake::mock(TokenStorageInterface::class);
        $this->dashboardMgr = \Phake::mock(DashboardManager::class);

        $this->provider = new ConfigMergersProvider(
            $this->container,
            $this->contributor,
            $this->tokenStorage,
            $this->dashboardMgr
        );
    }

    public function testItems()
    {
        $result = $this->provider->getItems();

        $this->assertTrue(is_array($result));
        $this->assertSame($this->provider, $result[0]);
    }

    public function testMerge_NoItems()
    {
        $user = \Phake::mock(User::class);

        $this->teachTokenStorage($user);

        \Phake::when($this->dashboardMgr)
            ->getDefaultDashboards($user)
            ->thenReturn([])
        ;
        \Phake::when($this->dashboardMgr)
            ->getUserDashboards($user)
            ->thenReturn([])
        ;

        $result = $this->provider->merge(array('foo' => 'foo-val'));

        $this->assertArrayHasKey('foo', $result);
        $this->assertEquals('foo-val', $result['foo']);

        $this->assertArrayHasKey('modera_backend_dashboard', $result);
        $this->assertTrue(is_array($result['modera_backend_dashboard']));

        $config = $result['modera_backend_dashboard'];

        $this->assertArrayHasKey('dashboards', $config);
        $this->assertEquals(1, count($config['dashboards'])); // default dashboard

        $this->assertValidDashboard($config['dashboards'][0]);
    }

    /**
     * @group MPFE-936
     */
    public function testMerge_noDefaultDashboard()
    {
        $user = \Phake::mock(User::class);

        $this->teachTokenStorage($user);

        $dashboard = $this->createDashboard('name1');
        \Phake::when($dashboard)->getLabel()->thenReturn('label1');
        \Phake::when($dashboard)->getUiClass()->thenReturn('class1');
        \Phake::when($dashboard)->getDescription()->thenReturn('desc1');
        \Phake::when($dashboard)->getIcon()->thenReturn('icon1');
        \Phake::when($dashboard)->isAllowed($this->container)->thenReturn(true);

        \Phake::when($this->dashboardMgr)
            ->getDefaultDashboards($user)
            ->thenReturn([])
        ;
        \Phake::when($this->dashboardMgr)
            ->getUserDashboards($user)
            ->thenReturn([$dashboard])
        ;

        $result = $this->provider->merge(array('foo' => 'foo-val'));

        $this->assertArrayHasKey('foo', $result);
        $this->assertEquals('foo-val', $result['foo']);

        $this->assertArrayHasKey('modera_backend_dashboard', $result);
        $this->assertTrue(is_array($result['modera_backend_dashboard']));

        $config = $result['modera_backend_dashboard'];

        $this->assertArrayHasKey('dashboards', $config);
        $this->assertEquals(2, count($config['dashboards']));

        $this->assertValidDashboard($config['dashboards'][0]);
        $this->assertFalse($config['dashboards'][0]['default']);

        $this->assertValidDashboard($config['dashboards'][1]);
        $this->assertTrue($config['dashboards'][1]['default']);
    }

    private function assertValidDashboard($dashboard)
    {
        $this->assertTrue(is_array($dashboard));
        $this->assertArrayHasKey('name', $dashboard);
        $this->assertArrayHasKey('uiClass', $dashboard);
        $this->assertArrayHasKey('label', $dashboard);
        $this->assertArrayHasKey('default', $dashboard);
    }

    private function createDashboard($name)
    {
        $item = \Phake::mock(DashboardInterface::class);

        \Phake::when($item)->getName()->thenReturn($name);

        return $item;
    }

    public function testMerge_HasItems()
    {
        $user = \Phake::mock(User::class);

        $this->teachTokenStorage($user);

        $dashboard = $this->createDashboard('name1');
        \Phake::when($dashboard)->getLabel()->thenReturn('label1');
        \Phake::when($dashboard)->getUiClass()->thenReturn('class1');
        \Phake::when($dashboard)->getDescription()->thenReturn('desc1');
        \Phake::when($dashboard)->getIcon()->thenReturn('icon1');
        \Phake::when($dashboard)->isAllowed($this->container)->thenReturn(true);

        \Phake::when($this->dashboardMgr)
            ->getDefaultDashboards($user)
            ->thenReturn([$dashboard])
        ;
        \Phake::when($this->dashboardMgr)
            ->getUserDashboards($user)
            ->thenReturn([$dashboard])
        ;

        $result = $this->provider->merge(array('foo' => 'foo-val'));

        $this->assertArrayHasKey('foo', $result);
        $this->assertEquals('foo-val', $result['foo']);

        $this->assertArrayHasKey('modera_backend_dashboard', $result);
        $this->assertTrue(is_array($result['modera_backend_dashboard']));

        $config = $result['modera_backend_dashboard'];

        $this->assertArrayHasKey('dashboards', $config);
        $this->assertEquals(1, count($config['dashboards'])); // default dashboard

        $this->assertEquals(array(
            'name' => 'name1',
            'label' => 'label1',
            'uiClass' => 'class1',
            'description' => 'desc1',
            'iconCls' => 'icon1',
            'default' => true,
        ), $config['dashboards'][0]);

        \Phake::verify($dashboard)->isAllowed($this->container);
    }

    public function testMerge_HasItems_NotAllowed()
    {
        $user = \Phake::mock(User::class);

        $this->teachTokenStorage($user);

        $dashboard1 = $this->createDashboard('name1');
        $dashboard2 = $this->createDashboard('name2');

        \Phake::when($dashboard1)
            ->isAllowed($this->container)
            ->thenReturn(true)
        ;
        \Phake::when($dashboard2)
            ->isAllowed($this->container)
            ->thenReturn(false)
        ;

        \Phake::when($this->dashboardMgr)
            ->getDefaultDashboards($user)
            ->thenReturn([$dashboard1])
        ;
        \Phake::when($this->dashboardMgr)
            ->getUserDashboards($user)
            ->thenReturn([$dashboard1, $dashboard2])
        ;

        $result = $this->provider->merge(array('foo' => 'foo-val'));

        $this->assertArrayHasKey('foo', $result);
        $this->assertEquals('foo-val', $result['foo']);

        $this->assertArrayHasKey('modera_backend_dashboard', $result);
        $this->assertTrue(is_array($result['modera_backend_dashboard']));

        $config = $result['modera_backend_dashboard'];

        $this->assertArrayHasKey('dashboards', $config);
        $this->assertEquals(1, count($config['dashboards'])); // default dashboard

        $this->assertEquals('name1', $config['dashboards'][0]['name']);

        \Phake::verify($dashboard1)->isAllowed($this->container);
        \Phake::verify($dashboard2)->isAllowed($this->container);
    }

    public function testGetters()
    {
        $this->assertSame($this->contributor, $this->provider->getDashboardProvider());
    }

    private function teachTokenStorage($user)
    {
        $token = \Phake::mock(TokenInterface::class);
        \Phake::when($token)
            ->getUser()
            ->thenReturn($user)
        ;

        \Phake::when($this->tokenStorage)
            ->getToken()
            ->thenReturn($token)
        ;
    }
}

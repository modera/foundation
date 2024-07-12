<?php

namespace Modera\BackendDashboardBundle\Tests\Unit;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Modera\BackendDashboardBundle\Dashboard\DashboardInterface;
use Modera\BackendDashboardBundle\Entity\GroupSettings;
use Modera\BackendDashboardBundle\Entity\UserSettings;
use Modera\BackendDashboardBundle\Service\DashboardManager;
use Modera\SecurityBundle\Entity\Group;
use Modera\SecurityBundle\Entity\GroupRepository;
use Modera\SecurityBundle\Entity\User;
use Modera\ExpanderBundle\Ext\ContributorInterface;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
class DashboardManagerTest extends \PHPUnit\Framework\TestCase
{
    public function testGetDashboardByName()
    {
        $dashboards = [$this->createDashboard('foo'), $this->createDashboard('bar')];

        $provider = \Phake::mock(ContributorInterface::class);
        \Phake::when($provider)
            ->getItems()
            ->thenReturn($dashboards)
        ;

        $em = \Phake::mock(EntityManager::class);

        $mgr = new DashboardManager($em, $provider);

        $this->assertSame($dashboards[0], $mgr->getDashboardByName('foo'));
        $this->assertSame($dashboards[1], $mgr->getDashboardByName('bar'));
        $this->assertNull($mgr->getDashboardByName('xxx'));
    }

    public function testGetUserDashboards()
    {
        $dashboards = [
            $this->createDashboard('foo'),
            $this->createDashboard('bar'),
            $this->createDashboard('baz'),
            $this->createDashboard('fofo'),
        ];

        $provider = \Phake::mock(ContributorInterface::class);
        \Phake::when($provider)
            ->getItems()
            ->thenReturn($dashboards)
        ;

        $group = \Phake::mock(Group::class);

        $user = \Phake::mock(User::class);
        \Phake::when($user)
            ->getGroups()
            ->thenReturn(new ArrayCollection([$group]))
        ;

        $userSettings = \Phake::mock(UserSettings::class);
        \Phake::when($userSettings)
            ->getDashboardSettings()
            ->thenReturn(array(
                'hasAccess' => ['foo', 'bababa'],
                'defaultDashboard' => 'baz',
            ))
        ;

        $groupSettings = \Phake::mock(GroupSettings::class);
        \Phake::when($groupSettings)
            ->getDashboardSettings()
            ->thenReturn(array(
                'hasAccess' => ['bar', 'blabla'],
                'defaultDashboard' => 'fofo',
            ))
        ;

        $userSettingsRepository = \Phake::mock(EntityRepository::class);
        \Phake::when($userSettingsRepository)
            ->findOneBy(array('user' => $user))
            ->thenReturn($userSettings)
        ;

        $groupSettingsRepository = \Phake::mock(GroupRepository::class);
        \Phake::when($groupSettingsRepository)
            ->findOneBy(array('group' => $group))
            ->thenReturn($groupSettings)
        ;

        $em = \Phake::mock(EntityManager::class);
        \Phake::when($em)
            ->getRepository(UserSettings::class)
            ->thenReturn($userSettingsRepository)
        ;
        \Phake::when($em)
            ->getRepository(GroupSettings::class)
            ->thenReturn($groupSettingsRepository)
        ;

        // ---

        $mgr = new DashboardManager($em, $provider);

        $indexedUserDashboards = array();
        foreach ($mgr->getUserDashboards($user) as $dashboard) {
            $indexedUserDashboards[$dashboard->getName()] = $dashboard;
        }

        // "blabla" and "bababa" must have been ignored
        $this->assertEquals(4, count($indexedUserDashboards));
        $this->assertSame($dashboards[0], $indexedUserDashboards['foo']);
        $this->assertSame($dashboards[1], $indexedUserDashboards['bar']);
        $this->assertSame($dashboards[2], $indexedUserDashboards['baz']);
        $this->assertSame($dashboards[3], $indexedUserDashboards['fofo']);
    }

    public function testGetDefaultDashboards()
    {
        $dashboards = [
            $this->createDashboard('foo'),
            $this->createDashboard('bar'),
        ];

        $provider = \Phake::mock(ContributorInterface::class);
        \Phake::when($provider)
            ->getItems()
            ->thenReturn($dashboards)
        ;

        $group1 = \Phake::mock(Group::class);

        $user = \Phake::mock(User::class);
        \Phake::when($user)
            ->getGroups()
            ->thenReturn(new ArrayCollection([$group1]))
        ;

        $userSettings = \Phake::mock(UserSettings::class);
        \Phake::when($userSettings)
            ->getDashboardSettings()
            ->thenReturn(array(
                'defaultDashboard' => 'foo',
            ))
        ;

        $groupSettings1 = \Phake::mock(GroupSettings::class);
        \Phake::when($groupSettings1)
            ->getDashboardSettings()
            ->thenReturn(array(
                'defaultDashboard' => 'bar',
            ))
        ;

        $userSettingsRepository = \Phake::mock(EntityRepository::class);
        \Phake::when($userSettingsRepository)
            ->findOneBy(array('user' => $user))
            ->thenReturn($userSettings)
        ;

        $groupSettingsRepository = \Phake::mock(GroupRepository::class);
        \Phake::when($groupSettingsRepository)
            ->findOneBy(array('group' => $group1))
            ->thenReturn($groupSettings1)
        ;

        $em = \Phake::mock(EntityManager::class);
        \Phake::when($em)
            ->getRepository(UserSettings::class)
            ->thenReturn($userSettingsRepository)
        ;
        \Phake::when($em)
            ->getRepository(GroupSettings::class)
            ->thenReturn($groupSettingsRepository)
        ;

        // ---

        $mgr = new DashboardManager($em, $provider);

        $indexedUserDashboards = array();
        foreach ($mgr->getDefaultDashboards($user) as $dashboard) {
            $indexedUserDashboards[$dashboard->getName()] = $dashboard;
        }

        $this->assertEquals(2, count($indexedUserDashboards));
        $this->assertSame($dashboards[0], $indexedUserDashboards['foo']);
        $this->assertSame($dashboards[1], $indexedUserDashboards['bar']);
    }

    public function testGetDashboards()
    {
        $em = \Phake::mock(EntityManager::class);

        $dashboards = [
            $this->createDashboard('foo'),
            $this->createDashboard('bar'),
        ];

        $provider = \Phake::mock(ContributorInterface::class);
        \Phake::when($provider)
            ->getItems()
            ->thenReturn($dashboards)
        ;

        $mgr = new DashboardManager($em, $provider);

        $returnedDashboards = $mgr->getDashboards();

        $this->assertEquals(2, count($dashboards));
        $this->assertSame($dashboards[0], $returnedDashboards[0]);
        $this->assertSame($dashboards[1], $returnedDashboards[1]);
    }

    /**
     * @param string $name
     *
     * @return DashboardInterface
     */
    private function createDashboard($name)
    {
        $dashboard = \Phake::mock(DashboardInterface::class);

        \Phake::when($dashboard)
            ->getName()
            ->thenReturn($name)
        ;

        return $dashboard;
    }
}

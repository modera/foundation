<?php

namespace Modera\DynamicallyConfigurableMJRBundle\Tests\Unit\MJR;

use Modera\ConfigBundle\Config\ConfigurationEntryInterface;
use Modera\ConfigBundle\Manager\ConfigurationEntriesManagerInterface;
use Modera\DynamicallyConfigurableMJRBundle\MJR\MainConfig;
use Modera\DynamicallyConfigurableMJRBundle\ModeraDynamicallyConfigurableMJRBundle as Bundle;

class MainConfigTest extends \PHPUnit\Framework\TestCase
{
    private MainConfig $mc;

    private ConfigurationEntriesManagerInterface $mgr;

    public function setUp(): void
    {
        $this->mgr = \Phake::mock(ConfigurationEntriesManagerInterface::class);
        $this->mc = new MainConfig($this->mgr);
    }

    private function teachManager($expectedKey, $returnValue): void
    {
        $entry = \Phake::mock(ConfigurationEntryInterface::class);
        \Phake::when($entry)
            ->getValue()
            ->thenReturn($returnValue)
        ;

        \Phake::when($this->mgr)
            ->findOneByNameOrDie($expectedKey)
            ->thenReturn($entry)
        ;
    }

    public function testGetTitle(): void
    {
        $this->teachManager(Bundle::CONFIG_TITLE, 'footitle');

        $this->assertEquals('footitle', $this->mc->getTitle());
    }

    public function testGetUrl(): void
    {
        $this->teachManager(Bundle::CONFIG_URL, 'foourl');

        $this->assertEquals('foourl', $this->mc->getUrl());
    }

    public function testGetHomeSection(): void
    {
        $this->teachManager(Bundle::CONFIG_HOME_SECTION, 'foosection');

        $this->assertEquals('foosection', $this->mc->getHomeSection());
    }
}

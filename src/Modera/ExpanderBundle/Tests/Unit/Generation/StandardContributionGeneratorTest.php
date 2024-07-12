<?php

namespace Modera\ExpanderBundle\Tests\Unit\Generation;

use Modera\ExpanderBundle\Ext\ExtensionPoint;
use Modera\ExpanderBundle\Generation\StandardContributionGenerator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;

class StandardContributionGeneratorTest extends \PHPUnit\Framework\TestCase
{
    private string $dir;
    private array $mocks = [];

    public function setUp(): void
    {
        $this->dir = \sys_get_temp_dir().'/modera-expander-gentest';
        if (!\file_exists($this->dir)) {
            $fs = new Filesystem();

            $fs->mkdir([$this->dir, $this->dir.'/Resources/config']);

            $servicesXmlContents = <<<XML
<?xml version="1.0" ?>

<container xmlns="http://symfony.com/schema/dic/services"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

    <services>
    </services>
</container>
XML;
            \file_put_contents($this->dir.'/Resources/config/services.xml', $servicesXmlContents);
        }

        $bundle = \Phake::mock('Symfony\Component\HttpKernel\Bundle\BundleInterface');
        $ep = \Phake::mock(ExtensionPoint::class);
        $input = \Phake::mock('Symfony\Component\Console\Input\InputInterface');
        $output = \Phake::mock('Symfony\Component\Console\Output\OutputInterface');
        $helperSet = \Phake::mock('Symfony\Component\Console\Helper\HelperSet');

        \Phake
            ::when($bundle)
            ->getPath()
            ->thenReturn($this->dir)
        ;
        \Phake
            ::when($bundle)
            ->getName()
            ->thenReturn('ModeraExpanderDummyBundle')
        ;
        \Phake
            ::when($bundle)
            ->getNamespace()
            ->thenReturn('FooNamespace')
        ;

        \Phake::when($ep)
            ->getContributionTag()
            ->thenReturn('blah_foo_tag')
        ;

        $this->mocks = [$bundle, $ep, $input, $output, $helperSet];
    }

    public function tearDown(): void
    {
        $fs = new Filesystem();
        $fs->remove([$this->dir]);
    }

    public function testGenerate()
    {
        $g = new StandardContributionGenerator(['className' => 'FooContribution']);

        $g->generate($this->mocks[0], $this->mocks[1], $this->mocks[2], $this->mocks[3], $this->mocks[4]);

        $this->assertTrue(\file_exists($this->dir.'/Contributions/FooContribution.php'));
        $classContents = \file_get_contents($this->dir.'/Contributions/FooContribution.php');
        $this->assertTrue(false !== \strpos($classContents, 'namespace FooNamespace\\Contributions'));

        $this->assertTrue(\file_exists($this->dir.'/Resources/config/services.xml'));
        $servicesXmlContents = \file_get_contents($this->dir.'/Resources/config/services.xml');
        $this->assertTrue(false !== \strpos($servicesXmlContents, 'class="FooNamespace\\Contributions\\FooContribution"'));
        $this->assertTrue(false !== \strpos($servicesXmlContents, 'id="modera_expander_dummy.contributions.foo_contribution"'));
        $this->assertTrue(false !== \strpos($servicesXmlContents, '<tag name="blah_foo_tag" />'));
    }

    public function testGenerateWithQuestion()
    {
        $g = new StandardContributionGenerator([]);

        $dialogHelper = \Phake::mock('Symfony\Component\Console\Helper\QuestionHelper');
        \Phake
            ::when($dialogHelper)
            ->ask(\Phake::anyParameters())
            ->thenReturn('BarContribution')
        ;

        $helperSet = $this->mocks[4];
        \Phake
            ::when($helperSet)
            ->get('question')
            ->thenReturn($dialogHelper)
        ;

        $g->generate($this->mocks[0], $this->mocks[1], $this->mocks[2], $this->mocks[3], $this->mocks[4]);

        $this->assertTrue(\file_exists($this->dir.'/Resources/config/services.xml'));
        $servicesXmlContents = \file_get_contents($this->dir.'/Resources/config/services.xml');
        $this->assertTrue(false !== \strpos($servicesXmlContents, 'class="FooNamespace\\Contributions\\BarContribution"'));
        $this->assertTrue(false !== \strpos($servicesXmlContents, 'id="modera_expander_dummy.contributions.bar_contribution"'));
        $this->assertTrue(false !== \strpos($servicesXmlContents, '<tag name="blah_foo_tag" />'));
    }

    public function testIsValidClassName()
    {
        $g = new StandardContributionGenerator(array());

        $this->assertFalse($g->isValidClassName(''));
        $this->assertFalse($g->isValidClassName('Foo bar')); // there's a space
        $this->assertTrue($g->isValidClassName('Foobar'));
    }
}

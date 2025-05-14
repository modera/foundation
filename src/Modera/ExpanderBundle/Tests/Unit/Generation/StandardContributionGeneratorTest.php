<?php

namespace Modera\ExpanderBundle\Tests\Unit\Generation;

use Modera\ExpanderBundle\Ext\ExtensionPoint;
use Modera\ExpanderBundle\Generation\StandardContributionGenerator;
use Symfony\Component\Filesystem\Filesystem;

class StandardContributionGeneratorTest extends \PHPUnit\Framework\TestCase
{
    private string $dir;
    private array $mocks = [];

    public function setUp(): void
    {
        $this->dir = \sys_get_temp_dir().'/modera-expander-gentest';
        if (!\file_exists($this->dir)) {
            $fs = new Filesystem();
            $fs->mkdir([$this->dir]);
        }

        $bundle = \Phake::mock('Symfony\Component\HttpKernel\Bundle\BundleInterface');
        $ep = \Phake::mock(ExtensionPoint::class);
        $input = \Phake::mock('Symfony\Component\Console\Input\InputInterface');
        $output = \Phake::mock('Symfony\Component\Console\Output\OutputInterface');
        $helperSet = \Phake::mock('Symfony\Component\Console\Helper\HelperSet');

        \Phake::when($bundle)
            ->getPath()
            ->thenReturn($this->dir)
        ;
        \Phake::when($bundle)
            ->getName()
            ->thenReturn('ModeraExpanderDummyBundle')
        ;
        \Phake::when($bundle)
            ->getNamespace()
            ->thenReturn('FooNamespace')
        ;

        \Phake::when($ep)
            ->getId()
            ->thenReturn('blah_foo')
        ;

        $this->mocks = [$bundle, $ep, $input, $output, $helperSet];
    }

    public function tearDown(): void
    {
        $fs = new Filesystem();
        $fs->remove([$this->dir]);
    }

    public function testGenerate(): void
    {
        $g = new StandardContributionGenerator([
            'className' => 'FooContribution',
            'dirName' => 'Contributions',
        ]);

        $g->generate($this->mocks[0], $this->mocks[1], $this->mocks[2], $this->mocks[3], $this->mocks[4]);

        $this->assertTrue(\file_exists($this->dir.'/Contributions/FooContribution.php'));
        $classContents = \file_get_contents($this->dir.'/Contributions/FooContribution.php');
        $this->assertTrue(false !== \strpos($classContents, 'namespace FooNamespace\\Contributions'));
    }

    public function testGenerateWithQuestion(): void
    {
        $g = new StandardContributionGenerator([
            'dirName' => 'Contributions',
        ]);

        $dialogHelper = \Phake::mock('Symfony\Component\Console\Helper\QuestionHelper');
        \Phake::when($dialogHelper)
            ->ask(\Phake::anyParameters())
            ->thenReturn('BarContribution')
        ;

        $helperSet = $this->mocks[4];
        \Phake::when($helperSet)
            ->get('question')
            ->thenReturn($dialogHelper)
        ;

        $g->generate($this->mocks[0], $this->mocks[1], $this->mocks[2], $this->mocks[3], $this->mocks[4]);

        $this->assertTrue(\file_exists($this->dir.'/Contributions/BarContribution.php'));
        $classContents = \file_get_contents($this->dir.'/Contributions/BarContribution.php');
        $this->assertTrue(false !== \strpos($classContents, 'namespace FooNamespace\\Contributions'));
    }

    public function testIsValidClassName(): void
    {
        $g = new StandardContributionGenerator([]);

        $this->assertFalse($g->isValidClassName(''));
        $this->assertFalse($g->isValidClassName('Foo Bar')); // there's a space
        $this->assertTrue($g->isValidClassName('FooBar'));
    }
}

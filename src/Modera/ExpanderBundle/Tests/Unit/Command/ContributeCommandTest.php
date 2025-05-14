<?php

namespace Modera\ExpanderBundle\Tests\Unit\Command;

use Modera\ExpanderBundle\Command\ContributeCommand;
use Modera\ExpanderBundle\Ext\ExtensionPoint;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Here we are testing only a "happy path" scenario.
 */
class ContributeCommandTest extends \PHPUnit\Framework\TestCase
{
    public function testExecute(): void
    {
        $bundles = [
            \Phake::mock('Symfony\Component\HttpKernel\Bundle\BundleInterface'),
        ];

        $kernel = \Phake::mock('Symfony\Component\HttpKernel\KernelInterface');

        \Phake::when($kernel)
            ->getBundles()
            ->thenReturn($bundles)
        ;

        $app = new Application($kernel);
        $app->add(new ContributeCommand($kernel));

        /** @var ContributeCommand $command */
        $command = $app->find('modera:expander:contribute');

        $this->assertInstanceOf(ContributeCommand::class, $command);

        $generator = \Phake::mock('Modera\ExpanderBundle\Generation\ContributionGeneratorInterface');
        $command->setContributionGenerator($generator);

        $epId = 'foo-ep-id';
        $extensionPoint = new ExtensionPoint($epId);
        $command->setExtensionPoints([
            $extensionPoint->getId() => \serialize($extensionPoint),
        ]);

        $tester = new CommandTester($command);

        $tester->execute([
            'command' => 'modera:expander:contribute',
            'id' => $epId,
        ]);

        \Phake::verify($generator)->generate(
            \Phake::capture($targetBundle),
            \Phake::capture($targetExtensionPoint),
            \Phake::capture($targetInput),
            \Phake::capture($targetOutput),
            \Phake::capture($targetHelperSet)
        );

        $this->assertSame($bundles[0], $targetBundle);
        $this->assertSame(\serialize($extensionPoint), \serialize($targetExtensionPoint));
        $this->assertInstanceOf('Symfony\Component\Console\Input\InputInterface', $targetInput);
        $this->assertInstanceOf('Symfony\Component\Console\Output\OutputInterface', $targetOutput);
        $this->assertInstanceOf('Symfony\Component\Console\Helper\HelperSet', $targetHelperSet);
    }
}

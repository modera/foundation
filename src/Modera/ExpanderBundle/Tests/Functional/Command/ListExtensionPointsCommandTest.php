<?php

namespace Modera\ExpanderBundle\Tests\Functional\Command;

use Modera\ExpanderBundle\Command\ListExtensionPointsCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ListExtensionPointsCommandTest extends WebTestCase
{
    public function testExecute(): void
    {
        static::$class = static::getKernelClass(); // required to run with MONOLITH_TEST_SUITE
        $kernel = $this->createKernel();
        $app = new Application($kernel);

        $cmd = new ListExtensionPointsCommand();
        $cmd->setApplication(new Application($kernel));
        $app->add($cmd);

        $extensionPoints = $kernel->getContainer()->getParameter('modera_expander.extension_points');
        $cmd->setExtensionPoints($extensionPoints);

        $command = $app->find('modera:expander:list-extension-points');

        $this->assertInstanceOf(ListExtensionPointsCommand::class, $command);

        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName(), '--skip-question' => true]);

        $this->assertMatchesRegularExpression('/modera_expander.dummy_resources/', $commandTester->getDisplay());
        $this->assertMatchesRegularExpression('/modera_expander.blah_resources/', $commandTester->getDisplay());

        // ---

        // with filter specified:
        $commandTester->execute(['command' => $command->getName(), 'id-filter' => 'blah', '--skip-question' => true]);

        $this->assertMatchesRegularExpression('/modera_expander.blah_resources/', $commandTester->getDisplay());
        $this->assertDoesNotMatchRegularExpression('/modera_expander.dummy_resources/', $commandTester->getDisplay());
    }
}

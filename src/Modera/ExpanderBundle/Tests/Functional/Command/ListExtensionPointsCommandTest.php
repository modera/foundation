<?php

namespace Modera\ExpanderBundle\Tests\Functional\Command;

use Modera\ExpanderBundle\Command\ListExtensionPointsCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ListExtensionPointsCommandTest extends WebTestCase
{
    public function testExecute()
    {
        $kernel = $this->createKernel();

        $app = new Application($kernel);
        $app->add(new ListExtensionPointsCommand());

        $command = $app->find('modera:expander:list-extension-points');

        $this->assertInstanceOf(ListExtensionPointsCommand::class, $command);

        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName(), '--skip-question' => true]);

        $this->assertRegExp('/modera_expander.dummy_resources/', $commandTester->getDisplay());
        $this->assertRegExp('/modera_expander.blah_resources/', $commandTester->getDisplay());

        // ---

        // with filter specified:
        $commandTester->execute(['command' => $command->getName(), 'id-filter' => 'blah', '--skip-question' => true]);

        $this->assertRegExp('/modera_expander.blah_resources/', $commandTester->getDisplay());
        $this->assertNotRegExp('/modera_expander.dummy_resources/', $commandTester->getDisplay());
    }
}

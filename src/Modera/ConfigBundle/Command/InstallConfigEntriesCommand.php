<?php

namespace Modera\ConfigBundle\Command;

use Modera\ConfigBundle\Config\ConfigEntriesInstaller;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @copyright 2014 Modera Foundation
 */
#[AsCommand(
    name: 'modera:config:install-config-entries',
    description: 'Installs configuration-entries defined through extension-points mechanism',
)]
class InstallConfigEntriesCommand extends Command
{
    public function __construct(
        private ConfigEntriesInstaller $installer,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln(' >> Installing configuration-entries ...');

        $installedEntries = $this->installer->install();

        foreach ($installedEntries as $entry) {
            $output->writeln(\sprintf('  - %s ( %s )', $entry->getName(), $entry->getReadableName()));
        }
        if (0 === \count($installedEntries)) {
            $output->writeln(" >> There's nothing to install, aborting");
        } else {
            $output->writeln(' >> Done!');
        }

        return Command::SUCCESS;
    }
}

<?php

namespace Modera\SecurityBundle\Command;

use Modera\SecurityBundle\DataInstallation\PermissionAndCategoriesInstaller;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @copyright 2014 Modera Foundation
 */
#[AsCommand(
    name: 'modera:security:install-permissions',
    description: 'Installs permissions.',
)]
class InstallPermissionsCommand extends Command
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly PermissionAndCategoriesInstaller $dataInstaller,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // set locale to undefined, then we will receive translations from source code
        if (\method_exists($this->translator, 'setLocale')) {
            $this->translator->setLocale('__');
        }

        $stats = $this->dataInstaller->installPermissions();

        $output->writeln(' >> Installed: '.$stats['installed']);
        // $output->writeln(' >> Removed: '.$stats['removed']);

        return Command::SUCCESS;
    }
}

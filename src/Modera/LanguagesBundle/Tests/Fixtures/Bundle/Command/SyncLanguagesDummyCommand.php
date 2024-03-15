<?php

namespace Modera\LanguagesBundle\Tests\Fixtures\Bundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Modera\LanguagesBundle\Command\SyncLanguagesCommand;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class SyncLanguagesDummyCommand extends SyncLanguagesCommand
{
    private $dummyInput;

    protected function configure(): void
    {
        $this
            ->setName('modera:languages:config-sync-dummy')
            ->addArgument('config', InputArgument::OPTIONAL)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->dummyInput = $input;
        return parent::execute($input, $output);
    }

    protected function getConfigLanguages(): array
    {
        $config = $this->dummyInput->getArgument('config');

        if ($config) {
            $config = json_decode($config, true);
            if ('config-file' == $config) {
                return parent::getConfigLanguages();
            }

            return $config;
        } else {
            return array();
        }
    }
}

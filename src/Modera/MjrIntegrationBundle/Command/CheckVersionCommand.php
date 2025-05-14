<?php

namespace Modera\MjrIntegrationBundle\Command;

use Modera\MjrIntegrationBundle\DependencyInjection\ModeraMjrIntegrationExtension;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * @copyright 2014 Modera Foundation
 */
#[AsCommand(
    name: 'modera:mjr-integration:check-version',
    description: 'Command validated that specified version of MJR is currently installed',
)]
class CheckVersionCommand extends Command
{
    public function __construct(
        private readonly ParameterBagInterface $params,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('required-version', InputArgument::REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var array{'runtime_path': string} $config */
        $config = $this->params->get(ModeraMjrIntegrationExtension::CONFIG_KEY);

        $mjrPath = \implode(\DIRECTORY_SEPARATOR, [
            \getcwd(), 'web', \substr($config['runtime_path'], 1),
        ]);

        $path = $mjrPath.\DIRECTORY_SEPARATOR.'package.json';

        $packageJson = \file_get_contents($path);
        if (false === $packageJson) {
            throw new \RuntimeException('Unable to find file '.$path);
        }

        /** @var string $requiredVersion */
        $requiredVersion = $input->getArgument('required-version');
        $requiredVersion = \trim($requiredVersion);

        /** @var array{'version': string} $packageJson */
        $packageJson = \json_decode($packageJson, true);

        $currentVersion = $packageJson['version'];
        if ($currentVersion !== $requiredVersion) {
            $output->writeln(
                "<comment>You have old '$currentVersion' version of MJR, downloading a required '$requiredVersion' version.</comment>"
            );

            $url = 'https://mjr.modera.org/releases/mjr.tar.gz';

            $archive = \file_get_contents($url);
            if (false === $archive) {
                throw new \RuntimeException("Unable to download MJR from $url");
            }

            $downloadedMjrPath = \getcwd().\DIRECTORY_SEPARATOR.'mjr-'.$requiredVersion.'.tar.gz';

            \file_put_contents($downloadedMjrPath, $archive);

            $output->writeln(\sprintf(
                'New version of MJR has been downloaded, local path: <info>%s</info> . Please extract it to <info>%s</info>',
                $downloadedMjrPath,
                $mjrPath
            ));
        } else {
            $output->writeln('<info>You have latest version of MJR, no need to update it.<info>');
        }

        return Command::SUCCESS;
    }
}

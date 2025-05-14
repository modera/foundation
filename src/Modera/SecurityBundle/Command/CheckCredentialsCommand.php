<?php

namespace Modera\SecurityBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Modera\SecurityBundle\Entity\User;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @copyright 2019 Modera Foundation
 */
#[AsCommand(
    name: 'modera:security:check-credentials',
    description: 'Check user credentials.',
)]
class CheckCredentialsCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $hasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('property', InputArgument::OPTIONAL, '', 'username')
            ->addOption('identifier', null, InputOption::VALUE_REQUIRED)
            ->addOption('password', null, InputOption::VALUE_REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $property */
        $property = $input->getArgument('property');

        /** @var string $identifier */
        $identifier = $input->getOption('identifier');

        /** @var string $password */
        $password = $input->getOption('password');

        /** @var array<string, mixed> $criteria */
        $criteria = [
            $property => $identifier,
        ];

        $user = $this->em->getRepository(User::class)->findOneBy($criteria);

        if (!$user) {
            $output->writeln(\sprintf('<error>User with identifier "%s" not found!</error>', $identifier));

            return Command::FAILURE;
        }

        if (!$this->hasher->isPasswordValid($user, $password)) {
            $output->writeln('<error>Password not valid!</error>');

            return Command::INVALID;
        }

        $output->writeln('<info>Password is valid!</info>');

        return Command::SUCCESS;
    }
}

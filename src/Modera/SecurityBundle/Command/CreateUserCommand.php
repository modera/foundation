<?php

namespace Modera\SecurityBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Modera\SecurityBundle\Entity\User;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @copyright 2014 Modera Foundation
 */
#[AsCommand(
    name: 'modera:security:create-user',
    description: 'Allows to create a user that you can later user to authenticate.',
)]
class CreateUserCommand extends Command
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
            ->addOption('no-interactions', null, InputOption::VALUE_NONE)
            ->addOption('username', null, InputOption::VALUE_OPTIONAL)
            ->addOption('email', null, InputOption::VALUE_OPTIONAL)
            ->addOption('password', null, InputOption::VALUE_OPTIONAL)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $username */
        $username = $input->getOption('username');

        /** @var string $email */
        $email = $input->getOption('email');

        /** @var string $password */
        $password = $input->getOption('password');

        if (false === $input->getOption('no-interactions')) {
            /** @var QuestionHelper $questionHelper */
            $questionHelper = $this->getHelper('question');

            $output->writeln('<info>This command will let you to create a test user that you can user to authenticated to administration interface</info>');
            $output->write(PHP_EOL);

            /** @var string $username */
            $username = $questionHelper->ask($input, $output, new Question('<question>Username:</question> '));

            /** @var string $email */
            $email = $questionHelper->ask($input, $output, new Question('<question>Email:</question> '));

            do {
                /** @var string $password */
                $password = $questionHelper->ask($input, $output, $this->createHiddenQuestion('<question>Password:</question> '));
                $passwordConfirm = $questionHelper->ask($input, $output, $this->createHiddenQuestion('<question>Password again:</question> '));

                if ($password !== $passwordConfirm) {
                    $output->writeln('<error>Entered passwords do not match, please try again</error>');
                }
            } while ($password !== $passwordConfirm);

            $output->write(PHP_EOL);
        }

        $user = new User();
        $user->setEmail($email);
        $user->setUsername($username);
        $user->setPassword($this->hasher->hashPassword($user, $password));

        $this->em->persist($user);
        $this->em->flush();

        $output->writeln(\sprintf(
            '<info>Great success! User "%s" has been successfully created!</info>',
            $user->getUsername()
        ));

        return Command::SUCCESS;
    }

    private function createHiddenQuestion(string $text): Question
    {
        $question = new Question($text);
        $question->setHidden(true);

        return $question;
    }
}

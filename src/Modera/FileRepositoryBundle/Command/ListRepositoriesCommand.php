<?php

namespace Modera\FileRepositoryBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Modera\FileRepositoryBundle\Entity\Repository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @copyright 2014 Modera Foundation
 */
#[AsCommand(
    name: 'modera:file-repository:list',
    description: 'Shows all available repositories',
)]
class ListRepositoriesCommand extends Command
{
    use TableTrait;

    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $rows = [];
        foreach ($this->em->getRepository(Repository::class)->findAll() as $repository) {
            /** @var Repository $repository */
            $config = $repository->getConfig();

            $rows[] = [
                $repository->getId(),
                $repository->getName(),
                $repository->getLabel() ?: '-',
                $config['filesystem'],
                isset($config['overwrite_files']) && true === $config['overwrite_files'] ? 'Enabled' : 'Disabled',
                $config['storage_key_generator'],
            ];
        }

        $this->renderTable(
            $output,
            ['#', 'Name', 'Label', 'Filesystem', 'Overwrite files', 'Storage key generator'],
            $rows
        );

        return Command::SUCCESS;
    }
}

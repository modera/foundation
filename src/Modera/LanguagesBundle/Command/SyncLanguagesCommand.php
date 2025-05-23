<?php

namespace Modera\LanguagesBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Modera\LanguagesBundle\DependencyInjection\ModeraLanguagesExtension;
use Modera\LanguagesBundle\Entity\Language;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * From config to database.
 *
 * @copyright 2014 Modera Foundation
 */
#[AsCommand(
    name: 'modera:languages:config-sync',
    description: 'Synchronize languages config with database.',
)]
class SyncLanguagesCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ParameterBagInterface $params,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $languages = $this->getConfigLanguages();
        $dbLanguages = $this->em->getRepository(Language::class)->findAll();

        $updated = [];
        $tableRows = [];
        if (\count($dbLanguages)) {
            /** @var Language $dbLanguage */
            foreach ($dbLanguages as $dbLanguage) {
                $language = null;
                foreach ($languages as $_language) {
                    if ($_language['locale'] === $dbLanguage->getLocale()) {
                        $language = $_language;
                        break;
                    }
                }

                if (\is_array($language)) {
                    $updated[] = $language['locale'];
                    $dbLanguage->setEnabled($language['is_enabled'] ? true : false);
                } else {
                    $dbLanguage->setEnabled(false);
                }
                $this->em->persist($dbLanguage);
                $tableRows[] = $this->tableRow($dbLanguage);
            }
        }

        foreach ($languages as $language) {
            if (!\in_array($language['locale'], $updated)) {
                $dbLanguage = new Language();
                $dbLanguage->setLocale($language['locale']);
                $dbLanguage->setEnabled($language['is_enabled'] ? true : false);
                $this->em->persist($dbLanguage);
                $tableRows[] = $this->tableRow($dbLanguage);
            }
        }

        $this->em->flush();

        $table = new Table($output);
        $table->setHeaders(['locale', 'name', 'enabled']);
        $table->setRows($tableRows);
        $table->render();

        return Command::SUCCESS;
    }

    /**
     * @return array<int, array{'locale': string, 'is_enabled': bool}>
     */
    protected function getConfigLanguages(): array
    {
        /** @var array<int, array{'locale': string, 'is_enabled': bool}> $languages */
        $languages = $this->params->get(ModeraLanguagesExtension::CONFIG_KEY);

        return $languages;
    }

    /**
     * @return mixed[]
     */
    private function tableRow(Language $dbLanguage): array
    {
        return [
            $dbLanguage->getLocale(),
            $dbLanguage->getName(),
            $dbLanguage->isEnabled(),
        ];
    }
}

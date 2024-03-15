<?php

namespace Modera\TranslationsBundle\Command;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Modera\LanguagesBundle\Entity\Language;
use Modera\TranslationsBundle\Compiler\Adapter\AdapterInterface;
use Modera\TranslationsBundle\Entity\LanguageTranslationToken;
use Modera\TranslationsBundle\Service\Translator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * Takes tokens from database and compiles them back to SF files.
 *
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class CompileTranslationsCommand extends Command
{
    private ContainerInterface $container;

    /**
     * @required
     */
    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    protected function configure(): void
    {
        $this
            ->setName('modera:translations:compile')
            ->setDescription('Compile entries from database to resources.')
            ->addOption('adapter', null, InputOption::VALUE_REQUIRED, 'Compiler adapter')
            ->addOption('no-warmup', null, InputOption::VALUE_NONE, 'Do not warm up translations cache')
            ->addOption('only-translated', null, InputOption::VALUE_NONE, 'Compile only translated entries')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $catalogues = $this->extractCatalogues((bool) $input->getOption('only-translated'));
        if (\count($catalogues)) {
            /** @var ?string $adapterId */
            $adapterId = $input->getOption('adapter');
            $adapter = $this->getAdapter($adapterId);

            $output->writeln('<fg=red>Clearing old translations</>');
            $adapter->clear();

            $output->writeln('');
            $output->writeln('Dumping translations:');
            foreach ($catalogues as $locale => $catalogue) {
                $output->writeln('    <fg=green>'.$locale.'</>');
                $adapter->dump($catalogue);
            }
            $output->writeln('');

            if (!$input->getOption('no-warmup')) {
                $this->translationsCacheWarmUp();
            }

            $output->writeln('>>> Translations have been successfully compiled');
        } else {
            $output->writeln('>>> Nothing to compile');
        }

        return 0;
    }

    /**
     * @return MessageCatalogue[]
     */
    protected function extractCatalogues(bool $onlyTranslated = false): array
    {
        /** @var EntityManagerInterface $em */
        $em = $this->container->get('doctrine.orm.entity_manager');

        $qb = $em->createQueryBuilder();
        $qb->select('l')
            ->from(Language::class, 'l')
            ->where($qb->expr()->eq('l.isEnabled', ':isEnabled'))
            ->setParameter('isEnabled', true);

        /** @var array<int, string> $languages */
        $languages = [];
        /** @var array{'id': int, 'locale': string} $row */
        foreach ((array) $qb->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY) as $row) {
            $languages[$row['id']] = $row['locale'];
        }

        $qb = $em->createQueryBuilder();
        $qb->select('ltt.id, ltt.translation, IDENTITY(ltt.language) AS language, tt.domain, tt.tokenName')
            ->from(LanguageTranslationToken::class, 'ltt')
            ->leftJoin('ltt.translationToken', 'tt')
            ->where($qb->expr()->in('ltt.language', \array_keys($languages)))
            ->andWhere($qb->expr()->in('tt.isObsolete', ':isObsolete'))
            ->setParameter('isObsolete', false)
        ;

        if ($onlyTranslated) {
            $qb->andWhere($qb->expr()->in('ltt.isNew', ':isNew'))
                ->setParameter('isNew', false);
        }

        $catalogues = [];
        foreach (\array_values($languages) as $locale) {
            $catalogues[$locale] = new MessageCatalogue($locale);
        }

        foreach ((array) $qb->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY) as $row) {
            /** @var array{'language': int, 'domain': string, 'tokenName': string, 'translation': string} $row */
            /** @var string $locale */
            $locale = $languages[$row['language']];
            /** @var MessageCatalogue $catalogue */
            $catalogue = $catalogues[$locale];
            $catalogue->set($row['tokenName'], $row['translation'], $row['domain']);
        }

        return $catalogues;
    }

    /**
     * Clear translations cache dir.
     */
    protected function translationsCacheWarmUp(): void
    {
        /** @var string $cacheDir */
        $cacheDir = $this->container->getParameter('kernel.cache_dir');
        $this->getTranslator()->warmUp($cacheDir);
    }

    protected function getAdapter(?string $id = null): AdapterInterface
    {
        /** @var AdapterInterface $adapter */
        $adapter = $this->container->get($id ?: 'modera_translations.compiler.adapter');

        return $adapter;
    }

    protected function getTranslator(): Translator
    {
        /** @var Translator $translator */
        $translator = $this->container->get('modera_translations.service.translator');

        return $translator;
    }
}

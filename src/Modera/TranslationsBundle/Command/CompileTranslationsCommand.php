<?php

namespace Modera\TranslationsBundle\Command;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Modera\TranslationsBundle\Compiler\Adapter\AdapterInterface;
use Modera\TranslationsBundle\Entity\LanguageTranslationToken;
use Modera\TranslationsBundle\Service\Translator;
use Modera\LanguagesBundle\Entity\Language;

/**
 * Takes tokens from database and compiles them back to SF files.
 *
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class CompileTranslationsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('modera:translations:compile')
            ->setDescription('Compile translations from database.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $catalogues = $this->extractCatalogues();
        if (count($catalogues)) {
            $adapter = $this->getAdapter();

            $output->writeln('<fg=red>Clearing old translations</>');
            $adapter->clear();

            $output->writeln('');
            $output->writeln('Dumping translations:');
            foreach ($catalogues as $locale => $catalogue) {
                if (!count($catalogue->all())) {
                    continue;
                }

                $output->writeln('    <fg=green>' . $locale . '</>');
                $adapter->dump($catalogue);
            }
            $output->writeln('');

            $this->clearTranslationsCache();

            $output->writeln('>>> Translations have been successfully compiled');
        } else {
            $output->writeln('>>> Nothing to compile');
        }
    }

    /**
     * @return MessageCatalogue[]
     */
    protected function extractCatalogues()
    {
        /* @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $qb = $em->createQueryBuilder();
        $qb->select('l')
            ->from(Language::clazz(), 'l')
            ->where($qb->expr()->eq('l.isEnabled', ':isEnabled'))
            ->setParameter('isEnabled', true);

        $languages = [];
        foreach ($qb->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY) as $row) {
            $languages[$row['id']] = $row['locale'];
        }

        $qb = $em->createQueryBuilder();
        $qb->select('ltt.id, ltt.translation, IDENTITY(ltt.language) AS language, tt.domain, tt.tokenName')
            ->from(LanguageTranslationToken::clazz(), 'ltt')
            ->leftJoin('ltt.translationToken', 'tt')
            ->where($qb->expr()->in('ltt.language', array_keys($languages)))
            ->andWhere($qb->expr()->in('tt.isObsolete', ':isObsolete'))
            ->setParameter('isObsolete', false);

        $catalogues = array();
        foreach ($qb->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY) as $row) {
            $locale = $languages[$row['language']];

            if (!isset($catalogues[$locale])) {
                $catalogues[$locale] = new MessageCatalogue($locale);
            }

            /* @var MessageCatalogue $catalogue */
            $catalogue = $catalogues[$locale];
            $catalogue->set($row['tokenName'], $row['translation'], $row['domain']);
        }

        return $catalogues;
    }

    /**
     * Clear translations cache dir
     */
    protected function clearTranslationsCache()
    {
        $this->getTranslator()->warmUp($this->getContainer()->getParameter('kernel.cache_dir'));
    }

    /**
     * @return AdapterInterface
     */
    protected function getAdapter()
    {
        return $this->getContainer()->get('modera_translations.compiler.adapter');
    }

    /**
     * @return Translator
     */
    protected function getTranslator()
    {
        return $this->getContainer()->get('modera_translations.service.translator');
    }
}

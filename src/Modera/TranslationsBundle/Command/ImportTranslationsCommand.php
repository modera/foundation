<?php

namespace Modera\TranslationsBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Translation\Reader\TranslationReader;
use Symfony\Component\Translation\Catalogue\MergeOperation;
use Symfony\Component\Translation\Catalogue\TargetOperation;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Modera\TranslationsBundle\EventListener\LanguageTranslationTokenListener;
use Modera\TranslationsBundle\Handling\TranslationHandlerInterface;
use Modera\TranslationsBundle\Service\TranslationHandlersChain;
use Modera\TranslationsBundle\Entity\LanguageTranslationToken;
use Modera\TranslationsBundle\Entity\TranslationToken;
use Modera\LanguagesBundle\Entity\Language;

/**
 * From files to database.
 *
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class ImportTranslationsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('modera:translations:import')
            ->setDescription('Finds and imports translations from files to database.')
            ->addOption('strategy', null, InputOption::VALUE_REQUIRED, 'Import strategy')
            ->addOption('ignore-obsolete', null, InputOption::VALUE_NONE, 'Ignore marking messages as obsolete')
            ->addOption('mark-as-translated', null, InputOption::VALUE_NONE, 'Mark all imported translations as translated')
            ->addOption('from-scratch', null, InputOption::VALUE_NONE, 'Clean DB before start')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $batchSize = 20;

        $ignoreObsolete = $input->getOption('ignore-obsolete');
        $markAsTranslated = $input->getOption('mark-as-translated');
        $fromScratch = $input->getOption('from-scratch');

        /* @var LanguageTranslationTokenListener $listener */
        $listener = $this->getContainer()->get('modera_translations.event_listener.language_translation_token_listener');
        $listener->setActive(false);

        $printMessageNames = $output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE;

        /* @var Language[] $languages */
        $languages = $this->em()->getRepository(Language::clazz())->findBy(array(
            'isEnabled' => true,
        ));
        if (!count($languages)) {
            $languages = array($this->createAndReturnDefaultLanguage());
        }

        $imported = false;

        if ($fromScratch) {
            $this->cleanDatabaseTables();
        }

        $tokens = $this->getTokens();

        $new = array();
        $obsolete = array();
        foreach ($languages as $language) {
            $locale = $language->getLocale();

            try {
                $extractedCatalogue = $this->getExtractedCatalogue($input, $output, $locale);
            } catch (\RuntimeException $e) {
                $output->writeln($e->getMessage());
                return;
            }

            $databaseCatalogue = new MessageCatalogue($locale);
            foreach ($tokens as $domain => $arr) {
                foreach ($arr as $token) {
                    if ($token['isObsolete']) {
                        continue;
                    }

                    if (isset($token['languageTranslationTokens'])) {
                        foreach ($token['languageTranslationTokens'] as $ltt) {
                            $lang = $this->findLanguage($languages, $ltt['language']);
                            if ($lang && $lang->getLocale() == $locale) {
                                $databaseCatalogue->set(
                                    $token['tokenName'], $ltt['translation'], $token['domain']
                                );

                                break;
                            }
                        }
                    }
                }
            }

            // process catalogues
            $operation = new TargetOperation($databaseCatalogue, $extractedCatalogue);

            foreach ($operation->getDomains() as $domain) {
                $newMessages = $operation->getNewMessages($domain);
                $obsoleteMessages = !$ignoreObsolete ? $operation->getObsoleteMessages($domain) : [];

                // if tokenName is same, but translation was changed
                $updatedMessages = array();
                $allMessages = $operation->getMessages($domain);
                $extractedMessages = $extractedCatalogue->all($domain);

                foreach ($extractedMessages as $tokenName => $translation) {
                    if (!array_key_exists($tokenName, $newMessages) && array_key_exists($tokenName, $allMessages)) {
                        if ($extractedMessages[$tokenName] !== $allMessages[$tokenName]) {
                            $token = $this->findTranslationToken($tokens, $domain, $tokenName);
                            if ($token) {
                                $ltt = $this->findLanguageTranslationToken($token, $language->getId());
                                // if not translated yet
                                if ($ltt && $ltt['isNew']) {
                                    $updatedMessages[$tokenName] = $translation;
                                }
                            }
                        }
                    }
                }

                if (count($newMessages) || count($updatedMessages) || count($obsoleteMessages)) {
                    $imported = true;
                }

                if (count($newMessages) || count($updatedMessages)) {
                    if (!isset($new[$domain])) {
                        $new[$domain] = array();
                    }

                    if (count($newMessages)) {
                        $output->writeln(sprintf('  <info>New messages (domain: %s): %s</>', $domain, count($newMessages)));
                        if ($printMessageNames) {
                            $this->printMessages($output, $newMessages);
                        }
                    }

                    if (count($updatedMessages)) {
                        $output->writeln(sprintf('  <info>Updated messages (domain: %s): %s</>', $domain, count($updatedMessages)));
                        if ($printMessageNames) {
                            $this->printMessages($output, $updatedMessages);
                        }
                    }

                    foreach (array_merge($newMessages, $updatedMessages) as $tokenName => $translation) {
                        if (!isset($new[$domain][$tokenName])) {
                            $new[$domain][$tokenName] = array();
                        }
                        $new[$domain][$tokenName][] = array(
                            'translation' => $translation,
                            'language'    => $language->getId(),
                        );
                    }
                }

                if (count($obsoleteMessages)) {
                    $output->writeln(sprintf('  <fg=red>Obsolete messages (domain: %s): %s</>', $domain, count($obsoleteMessages)));
                    if ($printMessageNames) {
                        $this->printMessages($output, $obsoleteMessages);
                    }

                    foreach ($obsoleteMessages as $tokenName => $translation) {
                        $token = $this->findTranslationToken($tokens, $domain, $tokenName);
                        if ($token && !$token['isObsolete']) {
                            $obsolete[] = $token['id'];
                        }
                    }
                }
            }
        }

        if ($imported) {
            if (count($new)) {
                // insert translation tokens
                $insertTranslationTokens = array();
                foreach ($new as $domain => $translationTokens) {
                    foreach ($translationTokens as $tokenName => $arr) {
                        $token = $this->findTranslationToken($tokens, $domain, $tokenName);
                        if (!$token) {
                            $insertTranslationTokens[] = array(
                                'domain'     => $domain,
                                'tokenName'  => $tokenName,
                            );
                        }
                    }
                }

                foreach ($insertTranslationTokens as $key => $data) {
                    $token = new TranslationToken();
                    $token
                        ->setDomain($data['domain'])
                        ->setTokenName($data['tokenName']);
                    $this->em()->persist($token);
                    if (($key % $batchSize) === 0) {
                        $this->em()->flush();
                        $this->em()->clear();
                    }
                }
                unset($insertTranslationTokens);
                $this->em()->flush();
                $this->em()->clear();

                $tokens = $this->getTokens();

                // insert/update language translation tokens
                $insertLanguageTranslationTokens = array();
                $updateLanguageTranslationTokens = array();
                foreach ($new as $domain => $translationTokens) {
                    foreach ($translationTokens as $tokenName => $arr) {
                        $token = $this->findTranslationToken($tokens, $domain, $tokenName);
                        if ($token) {
                            foreach ($arr as $data) {
                                $ltt = $this->findLanguageTranslationToken($token, $data['language']);
                                if (!$ltt) {
                                    $insertLanguageTranslationTokens[] = array(
                                        'language' => $data['language'],
                                        'translationToken' => $token['id'],
                                        'translation' => $data['translation'],
                                    );
                                } else if ($ltt['isNew']) {
                                    $updateLanguageTranslationTokens[] = array(
                                        'id' => $ltt['id'],
                                        'translation' => $data['translation'],
                                    );
                                }
                            }
                        }
                    }
                }

                foreach ($insertLanguageTranslationTokens as $key => $data) {
                    $languageToken = new LanguageTranslationToken();
                    $languageToken->setLanguage($this->em()->getReference(Language::clazz(), $data['language']));
                    $languageToken->setTranslationToken($this->em()->getReference(TranslationToken::clazz(), $data['translationToken']));
                    $languageToken->setTranslation($data['translation']);

                    if ($markAsTranslated) {
                        $languageToken->setNew(false);
                    }

                    $this->em()->persist($languageToken);
                    if (($key % $batchSize) === 0) {
                        $this->em()->flush();
                        $this->em()->clear();
                    }
                }
                unset($insertLanguageTranslationTokens);
                $this->em()->flush();
                $this->em()->clear();

                foreach ($updateLanguageTranslationTokens as $key => $data) {
                    $query = $this->em()->createQuery(
                        sprintf(
                            'UPDATE %s ltt SET ltt.translation = :translation %s WHERE ltt.id = :id',
                            LanguageTranslationToken::clazz(),
                            $markAsTranslated ? ', ltt.isNew = :isNew' : ''
                        )
                    );
                    $query->setParameter('translation', $data['translation']);
                    $query->setParameter('id', $data['id']);

                    if ($markAsTranslated) {
                        $query->setParameter('isNew', false);
                    }

                    $query->execute();
                }
                unset($updateLanguageTranslationTokens);

                $tokens = $this->getTokens();

                // update translation tokens
                $updateTranslationTokens = array();
                foreach ($new as $domain => $translationTokens) {
                    foreach ($translationTokens as $tokenName => $arr) {
                        $token = $this->findTranslationToken($tokens, $domain, $tokenName);
                        if ($token) {
                            $translations = $this->getTokenTranslations($token, $languages, $listener);
                            $updateTranslationTokens[] = array(
                                'id' => $token['id'],
                                'isObsolete' => false,
                                'translations' => $translations,
                            );
                            $token['translations'] = $translations;
                        }
                    }
                }

                foreach ($updateTranslationTokens as $key => $token) {
                    $query = $this->em()->createQuery(
                        sprintf(
                            'UPDATE %s tt SET tt.isObsolete = :isObsolete, tt.translations = :translations WHERE tt.id = :id',
                            TranslationToken::clazz()
                        )
                    );
                    $query->setParameter('isObsolete', $token['isObsolete']);
                    $query->setParameter('translations', json_encode($token['translations'], JSON_UNESCAPED_UNICODE));
                    $query->setParameter('id', $token['id']);
                    $query->execute();
                }
                unset($updateTranslationTokens);
            }

            // set obsolete
            if (count($obsolete)) {
                $query = $this->em()->createQuery(
                    sprintf(
                        'UPDATE %s tt SET tt.isObsolete = true WHERE tt.id IN(:ids)',
                        TranslationToken::clazz()
                    )
                );
                $query->setParameter('ids', $obsolete);
                $query->execute();
            }

            $output->writeln('>>> Translations have been successfully imported');
        } else {
            $output->writeln('>>> Nothing to import');
        }

        // update token translations
        $tokenTranslations = array();
        foreach ($tokens as $domain => $arr) {
            foreach ($arr as $token) {
                $translations = $this->getTokenTranslations($token, $languages, $listener);
                if (json_encode($translations, JSON_UNESCAPED_UNICODE) != $token['translations']) {
                    $tokenTranslations[] = array(
                        'id' => $token['id'],
                        'translations' => $translations,
                    );
                }
            }
        }

        if (count($tokenTranslations)) {
            foreach ($tokenTranslations as $key => $token) {
                $query = $this->em()->createQuery(
                    sprintf(
                        'UPDATE %s tt SET tt.translations = :translations WHERE tt.id = :id',
                        TranslationToken::clazz()
                    )
                );
                $query->setParameter('translations', json_encode($token['translations'], JSON_UNESCAPED_UNICODE));
                $query->setParameter('id', $token['id']);
                $query->execute();
            }
            unset($tokenTranslations);
        }

        $listener->setActive(true);
    }

    /**
     * @return string
     */
    protected function getImportStrategy()
    {
        $strategy = TranslationHandlerInterface::STRATEGY_SOURCE_TREE;

        if ($this->getContainer()->hasParameter('modera.translations_import_strategy')) {
            $strategy = $this->getContainer()->getParameter('modera.translations_import_strategy');
        }

        return $strategy;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param string          $locale
     * @return MessageCatalogue
     */
    protected function getExtractedCatalogue(InputInterface $input, OutputInterface $output, $locale)
    {
        $strategy = $input->getOption('strategy');
        if (!$strategy) {
            $strategy = $this->getImportStrategy();
        }

        if (TranslationHandlerInterface::STRATEGY_RESOURCE_FILES == $strategy) {
            return $this->getExtractedCatalogueByResourceFiles($input, $output, $locale);
        }

        return $this->getExtractedCatalogueBySourceTree($input, $output, $locale);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param string          $locale
     * @return MessageCatalogue
     */
    protected function getExtractedCatalogueBySourceTree(InputInterface $input, OutputInterface $output, $locale)
    {
        $catalogue = new MessageCatalogue($locale);

        /* @var TranslationHandlerInterface[] $handlers */
        $handlers = $this->getTranslationHandlersChain()->getHandlers();
        if (count($handlers) == 0) {
            throw new \RuntimeException('No translation handler are found, aborting ...');
        }

        foreach ($handlers as $handler) {
            if (in_array(TranslationHandlerInterface::STRATEGY_SOURCE_TREE, $handler->getStrategies())) {
                $bundleName = $handler->getBundleName();

                foreach ($handler->getSources() as $source) {
                    $extractedCatalogue = $handler->extract($source, $locale);

                    if (null !== $extractedCatalogue) {
                        $mergeOperation = new MergeOperation($catalogue, $extractedCatalogue);
                        $catalogue = $mergeOperation->getResult();

                        $output->writeln(
                            "Importing tokens for a locale <comment>$locale</> from a bundle <comment>$bundleName</> using $source"
                        );
                    }
                }
            }
        }

        return $catalogue;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param string          $locale
     * @return MessageCatalogue
     */
    protected function getExtractedCatalogueByResourceFiles(InputInterface $input, OutputInterface $output, $locale)
    {
        $markAsTranslated = $input->getOption('mark-as-translated');
        $extractedCatalogue = new MessageCatalogue($locale);
        $translationsDir = $this->getTranslationsDir();

        $fs = new Filesystem();
        if ($fs->exists($translationsDir)) {
            $this->getTranslationReader()->read($translationsDir, $extractedCatalogue);

            // load fallback translations
            $parts = explode('_', $locale);
            if (count($parts) > 1) {
                $fallbackCatalogue = new MessageCatalogue($parts[0]);
                $this->getTranslationReader()->read($translationsDir, $fallbackCatalogue);

                $mergeOperation = new MergeOperation(
                    $extractedCatalogue,
                    new MessageCatalogue($locale, $fallbackCatalogue->all())
                );
                $extractedCatalogue = $mergeOperation->getResult();
            }

            // if empty, load default translations
            if (count($extractedCatalogue->all()) == 0 && !$markAsTranslated) {
                $defaultLocale = $this->getTranslationsDefaultLocale();
                if ($defaultLocale) {
                    $defaultCatalogue = new MessageCatalogue($defaultLocale);
                    $this->getTranslationReader()->read($translationsDir, $defaultCatalogue);

                    $mergeOperation = new MergeOperation(
                        $extractedCatalogue,
                        new MessageCatalogue($locale, $defaultCatalogue->all())
                    );
                    $extractedCatalogue = $mergeOperation->getResult();
                }
            }

            $output->writeln(
                "Importing tokens for a locale <comment>$locale</> from <comment>$translationsDir</>"
            );
        }

        if (!$markAsTranslated) {
            /* @var TranslationHandlerInterface[] $handlers */
            $handlers = $this->getTranslationHandlersChain()->getHandlers();
            if (count($handlers)) {
                foreach ($handlers as $handler) {
                    if (in_array(TranslationHandlerInterface::STRATEGY_RESOURCE_FILES, $handler->getStrategies())) {
                        $bundleName = $handler->getBundleName();

                        foreach ($handler->getSources() as $source) {
                            $catalogue = $handler->extract($source, $locale);

                            if (null !== $catalogue) {
                                $mergeOperation = new MergeOperation($extractedCatalogue, $catalogue);
                                $extractedCatalogue = $mergeOperation->getResult();

                                $output->writeln(
                                    "Importing tokens for a locale <comment>$locale</> from a bundle <comment>$bundleName</> using $source"
                                );
                            }
                        }
                    }
                }
            }
        }

        return $extractedCatalogue;
    }

    /**
     * @return null|string
     */
    private function getTranslationsDefaultLocale()
    {
        $defaultLocale = null;

        if ($this->getContainer()->hasParameter('kernel.default_locale')) {
            $defaultLocale = $this->getContainer()->getParameter('kernel.default_locale');
        }

        if ($this->getContainer()->hasParameter('modera.translations_default_locale')) {
            $defaultLocale = $this->getContainer()->getParameter('modera.translations_default_locale');
        }

        return $defaultLocale;
    }

    /**
     * @return string
     */
    private function getTranslationsDir()
    {
        $rootDir = $this->getContainer()->getParameter('kernel.root_dir');
        $translationsDir = join(DIRECTORY_SEPARATOR, [ $rootDir, 'Resources', 'translations' ]);

        if ($this->getContainer()->hasParameter('modera.translations_dir')) {
            $translationsDir = $this->getContainer()->getParameter('modera.translations_dir');
        } else if ($this->getContainer()->hasParameter('translator.default_path')) {
            $translationsDir = $this->getContainer()->getParameter('translator.default_path');
        }

        return $translationsDir;
    }

    /**
     * @return TranslationReader
     */
    private function getTranslationReader()
    {
        return $this->getContainer()->get('modera_translations.translation.reader');
    }

    /**
     * @return TranslationHandlersChain
     */
    private function getTranslationHandlersChain()
    {
        return $this->getContainer()->get('modera_translations.service.translation_handlers_chain');
    }

    /**
     * @return EntityManagerInterface
     */
    private function em()
    {
        return $this->getContainer()->get('doctrine.orm.entity_manager');
    }

    /**
     * @param OutputInterface $output
     * @param $messages
     */
    private function printMessages(OutputInterface $output, $messages)
    {
        foreach ($messages as $token => $message) {
            $output->writeln("    * $message (token: $token)");
        }
    }

    /**
     * @return Language
     */
    private function createAndReturnDefaultLanguage()
    {
        $defaultLocale = $this->getContainer()->getParameter('locale');

        $language = new Language();
        $language->setLocale($defaultLocale);
        $language->setEnabled(true);

        $this->em()->persist($language);
        $this->em()->flush();

        return $language;
    }

    private function cleanDatabaseTables()
    {
        $query = $this->em()->createQuery(sprintf('DELETE %s ltt', LanguageTranslationToken::clazz()));
        $query->execute();

        $query = $this->em()->createQuery(sprintf('DELETE %s tt', TranslationToken::clazz()));
        $query->execute();
    }

    /**
     * @return array
     */
    private function getTokens()
    {
        $tokens = array();

        $query = $this->em()->createQuery(
            sprintf(
                'SELECT tt FROM %s tt',
                TranslationToken::clazz()
            )
        );
        $translationTokens = $query->getResult($query::HYDRATE_ARRAY);

        $query = $this->em()->createQuery(
            sprintf(
                'SELECT ltt, IDENTITY(ltt.language) as language, IDENTITY(ltt.translationToken) as translationToken FROM %s ltt',
                LanguageTranslationToken::clazz()
            )
        );
        $languageTranslationTokens = $query->getResult($query::HYDRATE_ARRAY);

        foreach ($languageTranslationTokens as $ltt) {
            foreach ($translationTokens as $key => $tt) {
                if ($tt['id'] == $ltt['translationToken']) {
                    if (!isset($translationTokens[$key]['languageTranslationTokens'])) {
                        $translationTokens[$key]['languageTranslationTokens'] = array();
                    }

                    $translationTokens[$key]['languageTranslationTokens'][] = array_merge($ltt[0], array(
                        'language' => $ltt['language'],
                    ));

                    break;
                }
            }
        }

        foreach ($translationTokens as $token) {
            if (!isset($tokens[$token['domain']])) {
                $tokens[$token['domain']] = array();
            }

            $tokens[$token['domain']][] = $token;
        }

        return $tokens;
    }

    /**
     * @param array $token
     * @param array $languages
     * @param LanguageTranslationTokenListener $listener
     * @return array
     */
    private function getTokenTranslations(array $token, array $languages, LanguageTranslationTokenListener $listener)
    {
        $translations = array();
        if (isset($token['languageTranslationTokens'])) {
            foreach ($token['languageTranslationTokens'] as $ltt) {
                $lang = $this->findLanguage($languages, $ltt['language']);
                if ($lang) {
                    $languageToken = new LanguageTranslationToken();
                    $languageToken->setNew($ltt['isNew']);
                    $languageToken->setLanguage($lang);
                    $languageToken->setTranslation($ltt['translation']);
                    $translations[$lang->getId()] = $listener->hydrateLanguageTranslationToken($languageToken);
                    $translations[$lang->getId()]['id'] = $ltt['id'];
                }
            }
        }

        return $translations;
    }

    /**
     * @param array $languages
     * @param $languageId
     * @return Language|null
     */
    private function findLanguage(array $languages, $languageId)
    {
        /* @var Language[] $languages */
        foreach ($languages as $language) {
            if ($languageId == $language->getId()) {
                return $language;
            }
        }

        return null;
    }

    /**
     * @param array $tokens
     * @param string $domain
     * @param string $tokenName
     * @return array|null
     */
    private function findTranslationToken(array $tokens, $domain, $tokenName)
    {
        if (isset($tokens[$domain])) {
            foreach ($tokens[$domain] as $token) {
                if ($tokenName === $token['tokenName']) {
                    return $token;
                }
            }
        }

        return null;
    }

    /**
     * @param array|null $token
     * @param int $languageId
     * @return array|null
     */
    private function findLanguageTranslationToken(array $token = null, $languageId)
    {
        if ($token) {
            if (isset($token['languageTranslationTokens'])) {
                foreach ($token['languageTranslationTokens'] as $ltt) {
                    if ($languageId == $ltt['language']) {
                        return $ltt;
                    }
                }
            }
        }

        return null;
    }
}

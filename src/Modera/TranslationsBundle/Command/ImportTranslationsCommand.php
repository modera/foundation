<?php

namespace Modera\TranslationsBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Modera\LanguagesBundle\Entity\Language;
use Modera\TranslationsBundle\Entity\LanguageTranslationToken;
use Modera\TranslationsBundle\Entity\TranslationToken;
use Modera\TranslationsBundle\Handling\TranslationHandlerInterface;
use Modera\TranslationsBundle\Service\TranslationHandlersChain;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Translation\Catalogue\MergeOperation;
use Symfony\Component\Translation\Catalogue\TargetOperation;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Reader\TranslationReaderInterface;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * From files to database.
 *
 * @copyright 2014 Modera Foundation
 */
#[AsCommand(
    name: 'modera:translations:import',
    description: 'Finds and imports translations from files to database.',
)]
class ImportTranslationsCommand extends Command
{
    private ContainerInterface $container;

    #[Required]
    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    public function __construct(
        private readonly TranslationHandlersChain $translationHandlersChain,
        private readonly TranslationReaderInterface $translationReader,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('strategy', null, InputOption::VALUE_REQUIRED, 'Import strategy')
            ->addOption('ignore-obsolete', null, InputOption::VALUE_NONE, 'Ignore marking messages as obsolete')
            ->addOption('mark-as-translated', null, InputOption::VALUE_NONE, 'Mark all imported translations as translated')
            ->addOption('from-scratch', null, InputOption::VALUE_NONE, 'Clean DB before start')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $batchSize = 20;

        $ignoreObsolete = $input->getOption('ignore-obsolete');
        $markAsTranslated = $input->getOption('mark-as-translated');
        $fromScratch = $input->getOption('from-scratch');

        $printMessageNames = $output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE;

        /** @var Language[] $languages */
        $languages = $this->em()->getRepository(Language::class)->findAll();
        if (!\count($languages)) {
            $languages = [$this->createAndReturnDefaultLanguage()];
        }

        $imported = false;

        if ($fromScratch) {
            $this->cleanDatabaseTables();
        }

        $tokens = $this->getTokens();

        $new = [];
        $obsolete = [];
        foreach ($languages as $language) {
            if (!$language->isEnabled()) {
                continue;
            }

            $locale = $language->getLocale();

            try {
                $extractedCatalogue = $this->getExtractedCatalogue($input, $output, $locale);
            } catch (\RuntimeException $e) {
                $output->writeln($e->getMessage());

                return Command::FAILURE;
            }

            /** @var array<string, array<string, string>> $dbMessages */
            $dbMessages = [];
            foreach ($tokens as $domain => $arr) {
                foreach ($arr as $token) {
                    if ($token['isObsolete'] ?? null) {
                        continue;
                    }

                    if (\is_array($token['languageTranslationTokens'] ?? null)) {
                        /** @var array{'language': int, 'translation': string} $ltt */
                        foreach ($token['languageTranslationTokens'] as $ltt) {
                            $lang = $this->findLanguage($languages, $ltt['language']);
                            if ($lang && $locale === $lang->getLocale()) {
                                /** @var array<string, string> $token */
                                if (!isset($dbMessages[$token['domain']])) {
                                    $dbMessages[$token['domain']] = [];
                                }
                                $dbMessages[$token['domain']][$token['tokenName']] = $ltt['translation'];
                                break;
                            }
                        }
                    }
                }
            }

            $dbObsoleteMessages = [];
            $databaseCatalogue = new MessageCatalogue($locale);
            if (\count($dbMessages)) {
                foreach ($dbMessages as $domain => $messages) {
                    if (MessageCatalogue::INTL_DOMAIN_SUFFIX !== \substr($domain, -\strlen(MessageCatalogue::INTL_DOMAIN_SUFFIX))) {
                        if (isset($dbMessages[$domain.MessageCatalogue::INTL_DOMAIN_SUFFIX])) {
                            $intlMessages = $dbMessages[$domain.MessageCatalogue::INTL_DOMAIN_SUFFIX];
                            foreach ($messages as $tokenName => $translation) {
                                if (isset($intlMessages[$tokenName])) {
                                    if (!isset($dbObsoleteMessages[$domain])) {
                                        $dbObsoleteMessages[$domain] = [];
                                    }
                                    $dbObsoleteMessages[$domain][$tokenName] = $translation;
                                    unset($messages[$tokenName]);
                                }
                            }
                        }
                    }
                    $databaseCatalogue->add($messages, $domain);
                }
            }

            // process catalogues
            $operation = new TargetOperation($databaseCatalogue, $extractedCatalogue);

            foreach ($operation->getDomains() as $domain) {
                $newMessages = \array_filter($operation->getNewMessages($domain), function ($k) {
                    return !\is_int($k);
                }, \ARRAY_FILTER_USE_KEY);
                $obsoleteMessages = !$ignoreObsolete ? \array_merge(
                    \array_filter($operation->getObsoleteMessages($domain), function ($k) {
                        return !\is_int($k);
                    }, \ARRAY_FILTER_USE_KEY),
                    $dbObsoleteMessages[$domain] ?? []
                ) : [];

                // if tokenName is same, but translation was changed
                $updatedMessages = [];
                $allMessages = $operation->getMessages($domain);
                $extractedMessages = $extractedCatalogue->all($domain);

                foreach ($extractedMessages as $tokenName => $translation) {
                    if (MessageCatalogue::INTL_DOMAIN_SUFFIX !== \substr($domain, -\strlen(MessageCatalogue::INTL_DOMAIN_SUFFIX))) {
                        if ($extractedCatalogue->defines($tokenName, $domain.MessageCatalogue::INTL_DOMAIN_SUFFIX)) {
                            if (\array_key_exists($tokenName, $newMessages)) {
                                unset($newMessages[$tokenName]);
                            } elseif (isset($dbMessages[$domain]) && \array_key_exists($tokenName, $dbMessages[$domain])) {
                                if (!$ignoreObsolete && !\array_key_exists($tokenName, $obsoleteMessages)) {
                                    $obsoleteMessages[$tokenName] = $dbMessages[$domain][$tokenName];
                                }
                            }
                            continue;
                        } else {
                            if (\in_array($domain.MessageCatalogue::INTL_DOMAIN_SUFFIX, $operation->getDomains(), true)) {
                                if (isset($dbMessages[$domain]) && !\array_key_exists($tokenName, $dbMessages[$domain])) {
                                    $intlObsoleteMessages = $operation->getObsoleteMessages($domain.MessageCatalogue::INTL_DOMAIN_SUFFIX);
                                    if (\array_key_exists($tokenName, $intlObsoleteMessages)) {
                                        $newMessages[$tokenName] = $translation;
                                    }
                                }
                            }
                        }
                    }

                    if (!\array_key_exists($tokenName, $newMessages) && \array_key_exists($tokenName, $allMessages)) {
                        if ($extractedMessages[$tokenName] !== $allMessages[$tokenName]) {
                            $token = $this->findTranslationToken($tokens, $domain, $tokenName);
                            if ($token && $language->getId()) {
                                $ltt = $this->findLanguageTranslationToken($token, $language->getId());
                                // if not translated yet
                                if ($ltt && $ltt['isNew']) {
                                    $updatedMessages[$tokenName] = $translation;
                                }
                            }
                        }
                    }
                }

                if (\count($obsoleteMessages)) {
                    if (MessageCatalogue::INTL_DOMAIN_SUFFIX !== \substr($domain, -\strlen(MessageCatalogue::INTL_DOMAIN_SUFFIX))) {
                        if (\in_array($domain.MessageCatalogue::INTL_DOMAIN_SUFFIX, $operation->getDomains(), true)) {
                            $intlObsoleteMessages = $operation->getObsoleteMessages($domain.MessageCatalogue::INTL_DOMAIN_SUFFIX);
                            if (\count($intlObsoleteMessages)) {
                                foreach ($obsoleteMessages as $tokenName => $translation) {
                                    if (isset($intlObsoleteMessages[$tokenName])) {
                                        unset($obsoleteMessages[$tokenName]);
                                    }
                                }
                            }
                        }
                    }
                }

                if (\count($newMessages) || \count($updatedMessages) || \count($obsoleteMessages)) {
                    $imported = true;
                }

                if (\count($newMessages) || \count($updatedMessages)) {
                    if (!isset($new[$domain])) {
                        $new[$domain] = [];
                    }

                    if (\count($newMessages)) {
                        $output->writeln(\sprintf('  <info>New messages (domain: %s): %s</>', $domain, \count($newMessages)));
                        if ($printMessageNames) {
                            $this->printMessages($output, $newMessages);
                        }
                    }

                    if (\count($updatedMessages)) {
                        $output->writeln(\sprintf('  <info>Updated messages (domain: %s): %s</>', $domain, \count($updatedMessages)));
                        if ($printMessageNames) {
                            $this->printMessages($output, $updatedMessages);
                        }
                    }

                    foreach (\array_merge($newMessages, $updatedMessages) as $tokenName => $translation) {
                        if (!isset($new[$domain][$tokenName])) {
                            $new[$domain][$tokenName] = [];
                        }
                        $new[$domain][$tokenName][] = [
                            'translation' => $translation,
                            'language' => $language->getId(),
                        ];
                    }
                }

                if (\count($obsoleteMessages)) {
                    $output->writeln(\sprintf('  <fg=red>Obsolete messages (domain: %s): %s</>', $domain, \count($obsoleteMessages)));
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
            if (\count($new)) {
                // insert translation tokens
                $insertTranslationTokens = [];
                foreach ($new as $domain => $translationTokens) {
                    foreach ($translationTokens as $tokenName => $arr) {
                        $key = $domain.$tokenName;
                        if (!isset($insertTranslationTokens[$key])) {
                            /** @var string $domain */
                            $token = $this->findTranslationToken($tokens, $domain, $tokenName);
                            if (!$token) {
                                $insertTranslationTokens[$key] = [
                                    'domain' => $domain,
                                    'tokenName' => $tokenName,
                                ];
                            }
                        }
                    }
                }

                foreach (\array_values($insertTranslationTokens) as $key => $data) {
                    $token = new TranslationToken();
                    $token
                        ->setDomain($data['domain'])
                        ->setTokenName($data['tokenName'])
                    ;
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
                $insertLanguageTranslationTokens = [];
                $updateLanguageTranslationTokens = [];
                foreach ($new as $domain => $translationTokens) {
                    foreach ($translationTokens as $tokenName => $arr) {
                        /** @var string $domain */
                        $token = $this->findTranslationToken($tokens, $domain, $tokenName);
                        if ($token) {
                            foreach ($arr as $data) {
                                $ltt = $this->findLanguageTranslationToken($token, $data['language'] ?? 0);
                                if (!$ltt) {
                                    $insertLanguageTranslationTokens[] = [
                                        'language' => $data['language'],
                                        'translationToken' => $token['id'],
                                        'translation' => $data['translation'],
                                    ];
                                } elseif ($ltt['isNew']) {
                                    $updateLanguageTranslationTokens[] = [
                                        'id' => $ltt['id'],
                                        'translation' => $data['translation'],
                                    ];
                                }
                            }
                        }
                    }
                }

                foreach ($insertLanguageTranslationTokens as $key => $data) {
                    $languageToken = new LanguageTranslationToken();
                    /** @var Language $languageReference */
                    $languageReference = $this->em()->getReference(Language::class, $data['language']);
                    $languageToken->setLanguage($languageReference);
                    /** @var TranslationToken $translationTokenReference */
                    $translationTokenReference = $this->em()->getReference(TranslationToken::class, $data['translationToken']);
                    $languageToken->setTranslationToken($translationTokenReference);
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
                        \sprintf(
                            'UPDATE %s ltt SET ltt.translation = :translation %s WHERE ltt.id = :id',
                            LanguageTranslationToken::class,
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
                $updateTranslationTokens = [];
                foreach ($new as $domain => $translationTokens) {
                    foreach ($translationTokens as $tokenName => $arr) {
                        /** @var string $domain */
                        $token = $this->findTranslationToken($tokens, $domain, $tokenName);
                        if ($token) {
                            $updateTranslationTokens[] = [
                                'id' => $token['id'],
                                'isObsolete' => false,
                            ];
                        }
                    }
                }

                foreach ($updateTranslationTokens as $key => $token) {
                    $query = $this->em()->createQuery(
                        \sprintf(
                            'UPDATE %s tt SET tt.isObsolete = :isObsolete WHERE tt.id = :id',
                            TranslationToken::class
                        )
                    );
                    $query->setParameter('isObsolete', $token['isObsolete']);
                    $query->setParameter('id', $token['id']);
                    $query->execute();
                }
                unset($updateTranslationTokens);
            }

            // set obsolete
            if (count($obsolete)) {
                $query = $this->em()->createQuery(
                    \sprintf(
                        'UPDATE %s tt SET tt.isObsolete = true WHERE tt.id IN(:ids)',
                        TranslationToken::class
                    )
                );
                $query->setParameter('ids', $obsolete);
                $query->execute();
            }

            $output->writeln('>>> Translations have been successfully imported');
        } else {
            $output->writeln('>>> Nothing to import');
        }

        return Command::SUCCESS;
    }

    protected function getImportStrategy(): string
    {
        $strategy = TranslationHandlerInterface::STRATEGY_SOURCE_TREE;

        if ($this->container->hasParameter('modera.translations_import_strategy')) {
            /** @var string $strategy */
            $strategy = $this->container->getParameter('modera.translations_import_strategy');
        }

        return $strategy;
    }

    protected function getExtractedCatalogue(InputInterface $input, OutputInterface $output, string $locale): MessageCatalogue
    {
        /** @var string $strategy */
        $strategy = $input->getOption('strategy');
        if (!$strategy) {
            $strategy = $this->getImportStrategy();
        }

        if (TranslationHandlerInterface::STRATEGY_RESOURCE_FILES === $strategy) {
            return $this->getExtractedCatalogueByResourceFiles($input, $output, $locale);
        }

        return $this->getExtractedCatalogueBySourceTree($input, $output, $locale);
    }

    protected function getExtractedCatalogueBySourceTree(InputInterface $input, OutputInterface $output, string $locale): MessageCatalogue
    {
        $catalogue = new MessageCatalogue($locale);

        /** @var TranslationHandlerInterface[] $handlers */
        $handlers = $this->translationHandlersChain->getHandlers();
        if (0 === \count($handlers)) {
            throw new \RuntimeException('No translation handler are found, aborting ...');
        }

        foreach ($handlers as $handler) {
            if (\in_array(TranslationHandlerInterface::STRATEGY_SOURCE_TREE, $handler->getStrategies())) {
                $bundleName = $handler->getBundleName();

                foreach ($handler->getSources() as $source) {
                    $extractedCatalogue = $handler->extract($source, $locale);

                    if (null !== $extractedCatalogue) {
                        $mergeOperation = new MergeOperation($catalogue, $extractedCatalogue);
                        /** @var MessageCatalogue $catalogue */
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

    protected function getExtractedCatalogueByResourceFiles(InputInterface $input, OutputInterface $output, string $locale): MessageCatalogue
    {
        $markAsTranslated = $input->getOption('mark-as-translated');
        $extractedCatalogue = new MessageCatalogue($locale);
        $translationsDir = $this->getTranslationsDir();

        $fs = new Filesystem();
        if ($fs->exists($translationsDir)) {
            $this->translationReader->read($translationsDir, $extractedCatalogue);

            // load fallback translations
            $parts = \explode('_', $locale);
            if (\count($parts) > 1) {
                $fallbackCatalogue = new MessageCatalogue($parts[0]);
                $this->translationReader->read($translationsDir, $fallbackCatalogue);

                $mergeOperation = new MergeOperation(
                    $extractedCatalogue,
                    new MessageCatalogue($locale, $fallbackCatalogue->all())
                );
                /** @var MessageCatalogue $extractedCatalogue */
                $extractedCatalogue = $mergeOperation->getResult();
            }

            // if empty, load default translations
            if (0 === \count($extractedCatalogue->all()) && !$markAsTranslated) {
                $defaultLocale = $this->getTranslationsDefaultLocale();
                if ($defaultLocale) {
                    $defaultCatalogue = new MessageCatalogue($defaultLocale);
                    $this->translationReader->read($translationsDir, $defaultCatalogue);

                    $mergeOperation = new MergeOperation(
                        $extractedCatalogue,
                        new MessageCatalogue($locale, $defaultCatalogue->all())
                    );
                    /** @var MessageCatalogue $extractedCatalogue */
                    $extractedCatalogue = $mergeOperation->getResult();
                }
            }

            $output->writeln(
                "Importing tokens for a locale <comment>$locale</> from <comment>$translationsDir</>"
            );
        }

        if (!$markAsTranslated) {
            /** @var TranslationHandlerInterface[] $handlers */
            $handlers = $this->translationHandlersChain->getHandlers();
            if (\count($handlers)) {
                foreach ($handlers as $handler) {
                    if (\in_array(TranslationHandlerInterface::STRATEGY_RESOURCE_FILES, $handler->getStrategies())) {
                        $bundleName = $handler->getBundleName();

                        foreach ($handler->getSources() as $source) {
                            $catalogue = $handler->extract($source, $locale);

                            if (null !== $catalogue) {
                                $mergeOperation = new MergeOperation($extractedCatalogue, $catalogue);
                                /** @var MessageCatalogue $extractedCatalogue */
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

    private function getTranslationsDefaultLocale(): ?string
    {
        $defaultLocale = null;

        if ($this->container->hasParameter('kernel.default_locale')) {
            /** @var string $defaultLocale */
            $defaultLocale = $this->container->getParameter('kernel.default_locale');
        }

        if ($this->container->hasParameter('modera.translations_default_locale')) {
            /** @var string $defaultLocale */
            $defaultLocale = $this->container->getParameter('modera.translations_default_locale');
        }

        return $defaultLocale;
    }

    private function getTranslationsDir(): string
    {
        /** @var string $projectDir */
        $projectDir = $this->container->getParameter('kernel.project_dir');
        $translationsDir = \join(\DIRECTORY_SEPARATOR, [$projectDir, 'app', 'Resources', 'translations']);

        if ($this->container->hasParameter('modera.translations_dir')) {
            /** @var string $translationsDir */
            $translationsDir = $this->container->getParameter('modera.translations_dir');
        } elseif ($this->container->hasParameter('translator.default_path')) {
            /** @var string $translationsDir */
            $translationsDir = $this->container->getParameter('translator.default_path');
        }

        return $translationsDir;
    }

    private function em(): EntityManagerInterface
    {
        /** @var EntityManagerInterface $em */
        $em = $this->container->get('doctrine.orm.entity_manager');

        return $em;
    }

    /**
     * @param array<string, string> $messages
     */
    private function printMessages(OutputInterface $output, array $messages): void
    {
        foreach ($messages as $token => $message) {
            $output->writeln("    * $message (token: $token)");
        }
    }

    private function createAndReturnDefaultLanguage(): Language
    {
        $defaultLocale = 'en';
        if ($this->container->hasParameter('kernel.default_locale')) {
            /** @var string $defaultLocale */
            $defaultLocale = $this->container->getParameter('kernel.default_locale');
        }

        $language = new Language();
        $language->setLocale($defaultLocale);
        $language->setEnabled(true);

        $this->em()->persist($language);
        $this->em()->flush();

        return $language;
    }

    private function cleanDatabaseTables(): void
    {
        $query = $this->em()->createQuery(sprintf('DELETE %s ltt', LanguageTranslationToken::class));
        $query->execute();

        $query = $this->em()->createQuery(sprintf('DELETE %s tt', TranslationToken::class));
        $query->execute();
    }

    /**
     * @return array<string, array<int, array<mixed>>>
     */
    private function getTokens(): array
    {
        /** @var array<string, array<int, array<mixed>>> $tokens */
        $tokens = [];

        $query = $this->em()->createQuery(
            \sprintf(
                'SELECT tt FROM %s tt',
                TranslationToken::class
            )
        );
        /** @var array<array<string, mixed>> $translationTokens */
        $translationTokens = $query->getResult($query::HYDRATE_ARRAY);

        $query = $this->em()->createQuery(
            \sprintf(
                'SELECT ltt, IDENTITY(ltt.language) as language, IDENTITY(ltt.translationToken) as translationToken FROM %s ltt',
                LanguageTranslationToken::class
            )
        );

        /** @var array{
         *      0: array{
         *          'id': int,
         *          'isNew': bool,
         *          'translation': string,
         *      },
         *      'language': string,
         *      'translationToken': string,
         * }[] $languageTranslationTokens
         */
        $languageTranslationTokens = $query->getResult($query::HYDRATE_ARRAY);

        /** @var array<string, array<mixed>> $tmp */
        $tmp = [];
        foreach ($languageTranslationTokens as $ltt) {
            $languageId = (int) $ltt['language'];
            $translationTokenId = (int) $ltt['translationToken'];

            if (!isset($tmp[$translationTokenId])) {
                $tmp[$translationTokenId] = [];
            }

            /** @var array<mixed> $arr */
            $arr = $ltt[0];
            $tmp[$translationTokenId][] = \array_merge($arr, [
                'language' => $languageId,
            ]);
        }

        foreach ($translationTokens as $key => $tt) {
            if (isset($tmp[$tt['id']])) {
                if (!isset($translationTokens[$key]['languageTranslationTokens'])) {
                    $translationTokens[$key]['languageTranslationTokens'] = [];
                }
                $translationTokens[$key]['languageTranslationTokens'] = $tmp[$tt['id']];
            }
        }

        foreach ($translationTokens as $token) {
            /** @var string $domain */
            $domain = $token['domain'];
            if (!isset($tokens[$domain])) {
                $tokens[$domain] = [];
            }
            $tokens[$domain][] = $token;
        }

        unset($translationTokens, $languageTranslationTokens, $tmp);

        return $tokens;
    }

    /**
     * @param array<Language> $languages
     */
    private function findLanguage(array $languages, int $languageId): ?Language
    {
        foreach ($languages as $language) {
            if ($languageId == $language->getId()) {
                return $language;
            }
        }

        return null;
    }

    /**
     * @param array<string, array<mixed>> $tokens
     *
     * @return ?array<mixed>
     */
    private function findTranslationToken(array $tokens, string $domain, string $tokenName): ?array
    {
        if (\is_array($tokens[$domain] ?? null)) {
            foreach ($tokens[$domain] as $token) {
                /** @var array<mixed> $token */
                if ($tokenName === $token['tokenName']) {
                    return $token;
                }
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $token
     *
     * @return ?array<mixed>
     */
    private function findLanguageTranslationToken(array $token, int $languageId): ?array
    {
        if (\is_array($token['languageTranslationTokens'] ?? null)) {
            foreach ($token['languageTranslationTokens'] as $ltt) {
                /** @var array<mixed> $ltt */
                if ($languageId === $ltt['language']) {
                    return $ltt;
                }
            }
        }

        return null;
    }
}

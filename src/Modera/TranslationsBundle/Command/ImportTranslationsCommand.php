<?php

namespace Modera\TranslationsBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Catalogue\TargetOperation;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Modera\LanguagesBundle\Entity\Language;
use Modera\TranslationsBundle\Entity\TranslationToken;
use Modera\TranslationsBundle\Entity\LanguageTranslationToken;
use Modera\TranslationsBundle\Service\TranslationHandlersChain;
use Modera\TranslationsBundle\Handling\TranslationHandlerInterface;
use Modera\TranslationsBundle\EventListener\LanguageTranslationTokenListener;

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
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $batchSize = 20;

        /* @var LanguageTranslationTokenListener $listener */
        $listener = $this->getContainer()->get('modera_translations.event_listener.language_translation_token_listener');
        $listener->setActive(false);

        $printMessageNames = $output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE;

        /* @var TranslationHandlersChain $translationHandlersChain */
        $translationHandlersChain = $this->getContainer()->get('modera_translations.service.translation_handlers_chain');

        $languages = $this->em()->getRepository(Language::clazz())->findBy(array(
            'isEnabled' => true,
        ));
        if (!count($languages)) {
            $languages = array($this->createAndReturnDefaultLanguage());
        }

        $imported = false;

        /* @var TranslationHandlerInterface[] $handlers */
        $handlers = $translationHandlersChain->getHandlers();
        if (count($handlers) == 0) {
            $output->writeln('No translation handler are found, aborting ...');

            return;
        }

        $tokens = $this->getTokens();

        $new = array();
        $obsolete = array();
        foreach ($handlers as $handler) {
            $bundleName = $handler->getBundleName();

            foreach ($handler->getSources() as $source) {
                /* @var Language $language */
                foreach ($languages as $language) {
                    $locale = $language->getLocale();

                    $extractedCatalogue = $handler->extract($source, $locale);
                    if (null === $extractedCatalogue) {
                        continue;
                    }

                    $databaseCatalogue = new MessageCatalogue($locale);
                    if (isset($tokens[$bundleName]) && isset($tokens[$bundleName][$source])) {
                        foreach ($tokens[$bundleName][$source] as $token) {
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
                        $obsoleteMessages = $operation->getObsoleteMessages($domain);

                        if (count($newMessages) || count($obsoleteMessages)) {
                            $imported = true;

                            $output->writeln(
                                "Importing tokens for a locale <comment>$locale</> from a bundle <comment>$bundleName</> using $source and domain <comment>$domain</>"
                            );
                        }

                        if (count($newMessages)) {
                            if (!isset($new[$bundleName])) {
                                $new[$bundleName] = array();
                            }
                            if (!isset($new[$bundleName][$source])) {
                                $new[$bundleName][$source] = array();
                            }
                            if (!isset($new[$bundleName][$source][$domain])) {
                                $new[$bundleName][$source][$domain] = array();
                            }

                            $output->writeln(sprintf('  <info>New messages: %s</>', count($newMessages)));
                            if ($printMessageNames) {
                                $this->printMessages($output, $newMessages);
                            }

                            foreach ($newMessages as $tokenName => $translation) {
                                if (!isset($new[$bundleName][$source][$domain][$tokenName])) {
                                    $new[$bundleName][$source][$domain][$tokenName] = array();
                                }
                                $new[$bundleName][$source][$domain][$tokenName][] = array(
                                    'translation' => $translation,
                                    'language' => $language->getId(),
                                );
                            }
                        }

                        if (count($obsoleteMessages)) {
                            $output->writeln(sprintf('  <fg=red>Obsolete messages: %s</>', count($obsoleteMessages)));
                            if ($printMessageNames) {
                                $this->printMessages($output, $obsoleteMessages);
                            }

                            foreach ($obsoleteMessages as $tokenName => $translation) {
                                $token = $this->findTranslationToken($tokens, $bundleName, $source, $domain, $tokenName);
                                if ($token && !$token['isObsolete']) {
                                    $obsolete[] = $token['id'];
                                }
                            }
                        }
                    }
                }
            }
        }

        if ($imported) {
            if (count($new)) {
                // insert translation tokens
                $insertTranslationTokens = array();
                foreach ($new as $bundleName => $sources) {
                    foreach ($sources as $source => $domains) {
                        foreach ($domains as $domain => $translationTokens) {
                            foreach ($translationTokens as $tokenName => $arr) {
                                $token = $this->findTranslationToken($tokens, $bundleName, $source, $domain, $tokenName);
                                if (!$token) {
                                    $insertTranslationTokens[] = array(
                                        'source' => $source,
                                        'bundleName' => $bundleName,
                                        'domain' => $domain,
                                        'tokenName' => $tokenName,
                                    );
                                }
                            }
                        }
                    }
                }

                foreach ($insertTranslationTokens as $key => $data) {
                    $token = new TranslationToken();
                    $token
                        ->setSource($data['source'])
                        ->setBundleName($data['bundleName'])
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
                foreach ($new as $bundleName => $sources) {
                    foreach ($sources as $source => $domains) {
                        foreach ($domains as $domain => $translationTokens) {
                            foreach ($translationTokens as $tokenName => $arr) {
                                $token = $this->findTranslationToken($tokens, $bundleName, $source, $domain, $tokenName);
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
                    }
                }

                foreach ($insertLanguageTranslationTokens as $key => $data) {
                    $languageToken = new LanguageTranslationToken();
                    $languageToken->setLanguage($this->em()->getReference(Language::clazz(), $data['language']));
                    $languageToken->setTranslationToken($this->em()->getReference(TranslationToken::clazz(), $data['translationToken']));
                    $languageToken->setTranslation($data['translation']);
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
                            'UPDATE %s ltt SET ltt.translation = :translation WHERE ltt.id = :id',
                            LanguageTranslationToken::clazz()
                        )
                    );
                    $query->setParameter('translation', $data['translation']);
                    $query->setParameter('id', $data['id']);
                    $query->execute();
                }
                unset($updateLanguageTranslationTokens);

                $tokens = $this->getTokens();

                // update translation tokens
                $updateTranslationTokens = array();
                foreach ($new as $bundleName => $sources) {
                    foreach ($sources as $source => $domains) {
                        foreach ($domains as $domain => $translationTokens) {
                            foreach ($translationTokens as $tokenName => $arr) {
                                $token = $this->findTranslationToken($tokens, $bundleName, $source, $domain, $tokenName);
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
                    $query->setParameter('translations', json_encode($token['translations']));
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
                $query->setParameter('ids', implode(', ', $obsolete));
                $query->execute();
            }

            $output->writeln('>>> Translations have been successfully imported');
        } else {
            $output->writeln('>>> Nothing to import');
        }

        // update token translations
        $tokenTranslations = array();
        foreach ($tokens as $bundleName => $sources) {
            foreach ($sources as $source => $arr) {
                foreach ($arr as $token) {
                    $translations = $this->getTokenTranslations($token, $languages, $listener);
                    if ($translations != $token['translations']) {
                        $tokenTranslations[] = array(
                            'id' => $token['id'],
                            'translations' => $translations,
                        );
                    }
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
                $query->setParameter('translations', json_encode($token['translations']));
                $query->setParameter('id', $token['id']);
                $query->execute();
            }
            unset($tokenTranslations);
        }

        $listener->setActive(true);
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
            if (!isset($tokens[$token['bundleName']])) {
                $tokens[$token['bundleName']] = array();
            }
            if (!isset($tokens[$token['bundleName']][$token['source']])) {
                $tokens[$token['bundleName']][$token['source']] = array();
            }

            $tokens[$token['bundleName']][$token['source']][] = $token;
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
     * @param string $bundleName
     * @param string $source
     * @param string $domain
     * @param string $tokenName
     * @return array|null
     */
    private function findTranslationToken(array $tokens, $bundleName, $source, $domain, $tokenName)
    {
        if (isset($tokens[$bundleName]) && isset($tokens[$bundleName][$source])) {
            foreach ($tokens[$bundleName][$source] as $token) {
                if ($domain == $token['domain'] && $tokenName == $token['tokenName']) {
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

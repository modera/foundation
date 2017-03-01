<?php

namespace Modera\TranslationsBundle\Command;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Translation\Catalogue\TargetOperation;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Catalogue\DiffOperation;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Modera\LanguagesBundle\Entity\Language;
use Modera\TranslationsBundle\Entity\TranslationToken;
use Modera\TranslationsBundle\Entity\LanguageTranslationToken;
use Modera\TranslationsBundle\Service\TranslationHandlersChain;
use Modera\TranslationsBundle\Handling\TranslationHandlerInterface;

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

        $handlers = $translationHandlersChain->getHandlers();
        if (count($handlers) == 0) {
            $output->writeln('No translation handler are found, aborting ...');

            return;
        }

        foreach ($handlers as $handler) {
            /* @var TranslationHandlerInterface $handler */

            $bundleName = $handler->getBundleName();

            foreach ($handler->getSources() as $source) {
                $tokens = $this->em()->getRepository(TranslationToken::clazz())->findBy(array(
                    'source' => $source,
                    'bundleName' => $bundleName,
                ));

                /* @var Language $language */
                foreach ($languages as $language) {
                    $locale = $language->getLocale();

                    $extractedCatalogue = $handler->extract($source, $locale);
                    if (null === $extractedCatalogue) {
                        continue;
                    }

                    $currentCatalogue = new MessageCatalogue($locale);
                    /* @var TranslationToken $token */
                    foreach ($tokens as $token) {
                        if ($token->isObsolete()) {
                            continue;
                        }

                        foreach ($token->getLanguageTranslationTokens() as $ltt) {
                            /* @var LanguageTranslationToken $ltt */

                            $lang = $ltt->getLanguage();
                            if ($lang && $lang->getLocale() == $locale) {
                                $currentCatalogue->set($token->getTokenName(), $ltt->getTranslation(), $token->getDomain());

                                break;
                            }
                        }
                    }

                    // process catalogues
                    $operation = new TargetOperation($currentCatalogue, $extractedCatalogue);

                    foreach ($operation->getDomains() as $domain) {
                        $newMessages = $operation->getNewMessages($domain);
                        $obsoleteMessages = $operation->getObsoleteMessages($domain);

                        if (count($newMessages) || count($obsoleteMessages)) {
                            $imported = true;

                            $output->writeln(
                                "Importing a locale <comment>$locale</> from a bundle <comment>$bundleName</> using $source and domain $domain"
                            );
                        }

                        if (count($newMessages)) {
                            $output->writeln(sprintf('  <info>New messages: %s</>', count($newMessages)));
                            if ($printMessageNames) {
                                $this->printMessages($output, $newMessages);
                            }

                            foreach ($newMessages as $tokenName => $translation) {
                                $token = $this->findOrCreateTranslationToken(
                                    $source, $bundleName, $domain, $tokenName
                                );
                                $token->setObsolete(false);

                                $ltt = $this->em()->getRepository(LanguageTranslationToken::clazz())->findOneBy(array(
                                    'language' => $language,
                                    'translationToken' => $token,
                                    'translation' => $translation,
                                ));
                                if (!$ltt) {
                                    $ltt = new LanguageTranslationToken();
                                    $ltt->setLanguage($language);
                                    $token->addLanguageTranslationToken($ltt);
                                }

                                if ($ltt->isNew()) {
                                    $ltt->setTranslation($translation);
                                }

                                $this->em()->persist($token);
                            }
                            $this->em()->flush();
                        }

                        if (count($obsoleteMessages)) {
                            $output->writeln(sprintf('  <fg=red>Obsolete messages: %s</>', count($obsoleteMessages)));
                            if ($printMessageNames) {
                                $this->printMessages($output, $obsoleteMessages);
                            }

                            foreach ($obsoleteMessages as $tokenName => $translation) {
                                $token = $this->findOrCreateTranslationToken(
                                    $source, $bundleName, $domain, $tokenName
                                );
                                $token->setObsolete(true);
                                $this->em()->persist($token);
                            }
                            $this->em()->flush();
                        }
                    }
                }
            }
        }

        if ($imported) {
            $output->writeln('>>> Translations have been successfully imported');
        } else {
            $output->writeln('>>> Nothing to import');
        }
    }

    private function printMessages(OutputInterface $output, $messages)
    {
        foreach ($messages as $message) {
            $output->writeln('    * '.$message);
        }
    }

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
     * @return EntityManagerInterface
     */
    private function em()
    {
        return $this->getContainer()->get('doctrine.orm.entity_manager');
    }

    private function findOrCreateTranslationToken($source, $bundleName, $domain, $tokenName)
    {
        $token = $this->em()->getRepository(TranslationToken::clazz())->findOneBy(array(
            'source' => $source,
            'bundleName' => $bundleName,
            'domain' => $domain,
            'tokenName' => $tokenName,
        ));

        if (!$token) {
            $token = new TranslationToken();
            $token
                ->setSource($source)
                ->setBundleName($bundleName)
                ->setDomain($domain)
                ->setTokenName($tokenName)
            ;

            $this->em()->persist($token);
            $this->em()->flush();
        }

        return $token;
    }
}
<?php

namespace Modera\TranslationsBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Translation\Writer\TranslationWriter;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Modera\TranslationsBundle\Entity\LanguageTranslationToken;
use Modera\TranslationsBundle\Entity\TranslationToken;

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
            ->setDescription('Compile language files from database.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $outputFormat = 'yml';

        /* @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        /* @var TranslationWriter $writer */
        $writer = $this->getContainer()->get('translation.writer');

        // check format
        $supportedFormats = $writer->getFormats();
        if (!in_array($outputFormat, $supportedFormats)) {
            $output->writeln('<error>Wrong output format</error>');
            $output->writeln('>>> Supported formats are '.implode(', ', $supportedFormats).'.');

            return 1;
        }

        $tokens = $em->getRepository(TranslationToken::clazz())->findBy(array(
            'isObsolete' => false,
        ));

        $bundles = array();
        /* @var TranslationToken $token */
        foreach ($tokens as $token) {
            $bundleName = $token->getBundleName();

            if (!isset($bundles[$bundleName])) {
                $bundles[$bundleName] = array();
            }

            $ltts = $token->getLanguageTranslationTokens();

            /* @var LanguageTranslationToken $ltt */
            foreach ($ltts as $ltt) {
                if (!$ltt->getLanguage()->getEnabled()) {
                    continue;
                }

                $locale = $ltt->getLanguage()->getLocale();

                if (!isset($bundles[$bundleName][$locale])) {
                    $bundles[$bundleName][$locale] = new MessageCatalogue($locale);
                }

                $catalogue = $bundles[$bundleName][$locale];
                $catalogue->set($token->getTokenName(), $ltt->getTranslation(), $token->getDomain());
            }
        }

        if (count($bundles)) {
            $fs = new Filesystem();

            $rootDir = $this->getContainer()->getParameter('kernel.root_dir');
            $basePath = dirname($this->normalizePath($rootDir));

            $translationsDir = join(DIRECTORY_SEPARATOR, array($rootDir, 'Resources', 'translations'));
            if ($this->getContainer()->hasParameter('modera.translations_dir')) {
                $translationsDir = $this->getContainer()->getParameter('modera.translations_dir');
            }

            foreach ($bundles as $bundleName => $catalogues) {
                if (!count($catalogues)) {
                    continue;
                }

                $bundleTransPath = $translationsDir.'/'.$bundleName;
                $parts = explode($basePath . DIRECTORY_SEPARATOR, $this->normalizePath($bundleTransPath));
                if (count($parts) > 1) {
                    $bundleTransDir = $parts[1];
                } else {
                    $bundleTransDir = $parts[0];
                }

                $output->writeln('>>> '.$bundleName.': '.$bundleTransDir);

                if ($fs->exists($bundleTransPath)) {
                    $output->writeln('    <fg=red>Removing old files</>');
                    $fs->remove($bundleTransPath);
                }

                try {
                    if (!$fs->exists(dirname($bundleTransPath))) {
                        $fs->mkdir(dirname($bundleTransPath));
                        $fs->chmod(dirname($bundleTransPath), 0777);
                    }

                    $fs->mkdir($bundleTransPath);
                } catch (IOExceptionInterface $e) {
                    echo 'An error occurred while creating your directory at '.$e->getPath();
                }

                $output->writeln('    <fg=green>Creating new files</>');

                foreach ($catalogues as $locale => $catalogue) {
                    $writer->writeTranslations($catalogue, $outputFormat, array('path' => $bundleTransPath));
                }

                $fs->chmod($bundleTransPath, 0777, 0000, true);
            }

            $output->writeln('>>> Translations have been successfully compiled');
        } else {
            $output->writeln('>>> Nothing to compile');
        }
    }

    /**
     * @param $path
     * @return string
     */
    protected function normalizePath($path)
    {
        $path = str_replace('\\', '/', $path);
        $path = preg_replace('/\/+/', '/', $path);

        $parts = array();
        $segments = explode('/', $path);

        foreach ($segments as $segment) {
            if ($segment != '.') {
                $test = array_pop($parts);
                if (is_null($test)) {
                    $parts[] = $segment;
                } else if($segment == '..') {
                    if ($test == '..') {
                        $parts[] = $test;
                    }
                    if ($test == '..' || $test == '') {
                        $parts[] = $segment;
                    }
                } else {
                    $parts[] = $test;
                    $parts[] = $segment;
                }
            }
        }

        return implode(DIRECTORY_SEPARATOR, $parts);
    }
}

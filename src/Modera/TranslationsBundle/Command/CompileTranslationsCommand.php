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

        $catalogues = array();
        /* @var TranslationToken $token */
        foreach ($tokens as $token) {
            $ltts = $token->getLanguageTranslationTokens();

            /* @var LanguageTranslationToken $ltt */
            foreach ($ltts as $ltt) {
                if (!$ltt->getLanguage()->getEnabled()) {
                    continue;
                }

                $locale = $ltt->getLanguage()->getLocale();

                if (!isset($catalogues[$locale])) {
                    $catalogues[$locale] = new MessageCatalogue($locale);
                }

                $catalogue = $catalogues[$locale];
                $catalogue->set($token->getTokenName(), $ltt->getTranslation(), $token->getDomain());
            }
        }

        if (count($catalogues)) {
            $fs = new Filesystem();

            $rootDir = $this->getContainer()->getParameter('kernel.root_dir');
            $basePath = dirname($this->normalizePath($rootDir));

            $translationsDir = join(DIRECTORY_SEPARATOR, array($rootDir, 'Resources', 'translations'));
            if ($this->getContainer()->hasParameter('modera.translations_dir')) {
                $translationsDir = $this->getContainer()->getParameter('modera.translations_dir');
            }

            $transPath = $translationsDir;
            $parts = explode($basePath . DIRECTORY_SEPARATOR, $this->normalizePath($transPath));
            if (count($parts) > 1) {
                $transDir = $parts[1];
            } else {
                $transDir = $parts[0];
            }

            if ($fs->exists($transPath)) {
                $output->writeln('    <fg=red>Removing old files</>');
                $fs->remove($transPath);
            }

            foreach ($catalogues as $locale => $catalogue) {
                if (!count($catalogue)) {
                    continue;
                }

                $output->writeln('>>> '.$locale.': '.$transDir);

                try {
                    if (!$fs->exists(dirname($transPath))) {
                        $fs->mkdir(dirname($transPath));
                        $fs->chmod(dirname($transPath), 0777);
                    }
                } catch (IOExceptionInterface $e) {
                    echo 'An error occurred while creating your directory at '.$e->getPath();
                }

                $output->writeln('    <fg=green>Creating new files</>');

                $writer->writeTranslations($catalogue, $outputFormat, array('path' => $transPath));

                $fs->chmod($transPath, 0777, 0000, true);
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

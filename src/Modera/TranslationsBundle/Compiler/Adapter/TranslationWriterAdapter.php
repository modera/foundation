<?php

namespace Modera\TranslationsBundle\Compiler\Adapter;

use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\MessageCatalogueInterface;
use Symfony\Component\Translation\Writer\TranslationWriter;

/**
 * @copyright 2019 Modera Foundation
 */
class TranslationWriterAdapter implements AdapterInterface
{
    public function __construct(
        private readonly TranslationWriter $writer,
        private readonly string $translationsDir,
        private readonly string $cacheDir,
    ) {
    }

    public function clear(): void
    {
        $fs = new Filesystem();
        if ($fs->exists($this->translationsDir)) {
            foreach (Finder::create()->files()->in($this->translationsDir) as $file) {
                $fs->remove($file->getRealPath());
            }
        }
    }

    public function dump(MessageCatalogueInterface $catalogue): void
    {
        /** @var MessageCatalogue $catalogue */
        $catalogue = $catalogue;

        if (!\count($catalogue->all())) {
            return;
        }

        $outputFormat = 'yaml';

        // check format
        $supportedFormats = $this->writer->getFormats();
        if (!\in_array($outputFormat, $supportedFormats)) {
            throw new \RuntimeException(\sprintf('Wrong output format. Supported formats are %s.', \implode(', ', $supportedFormats)));
        }

        $fs = new Filesystem();

        try {
            if (!$fs->exists(\dirname($this->translationsDir))) {
                $fs->mkdir(\dirname($this->translationsDir));
                $fs->chmod(\dirname($this->translationsDir), 0777);
            }
        } catch (IOExceptionInterface $e) {
            throw new \RuntimeException(\sprintf('An error occurred while creating your directory at %s', $e->getPath()));
        }

        $this->writer->write($catalogue, $outputFormat, ['path' => $this->translationsDir]);
        $fs->chmod($this->translationsDir, 0777, 0000, true);

        if ($fs->exists($this->cacheDir)) {
            $filter = function (\SplFileInfo $file) use ($catalogue) {
                $prefix = 'catalogue.'.\preg_replace('/[^a-z0-9_]/i', '_', $catalogue->getLocale()).'.';

                return 0 === \strpos($file->getBasename(), $prefix);
            };

            foreach (Finder::create()->files()->filter($filter)->in($this->cacheDir) as $file) {
                $fs->remove($file->getRealPath());
            }
        }
    }

    public function loadCatalogue(string $locale): MessageCatalogueInterface
    {
        // return empty catalogue, as symfony will automatically load from translation files
        return new MessageCatalogue($locale);
    }
}

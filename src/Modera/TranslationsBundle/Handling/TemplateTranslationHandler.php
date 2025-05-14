<?php

namespace Modera\TranslationsBundle\Handling;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Translation\Catalogue\MergeOperation;
use Symfony\Component\Translation\Extractor\ExtractorInterface;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\MessageCatalogueInterface;
use Symfony\Component\Translation\Reader\TranslationReaderInterface;

/**
 * @copyright 2014 Modera Foundation
 */
class TemplateTranslationHandler implements TranslationHandlerInterface
{
    public const SOURCE_NAME = 'template';

    public function __construct(
        protected readonly KernelInterface $kernel,
        protected readonly TranslationReaderInterface $loader,
        protected readonly ExtractorInterface $extractor,
        protected readonly string $bundle,
    ) {
    }

    public function getBundleName(): string
    {
        return $this->bundle;
    }

    public function getStrategies(): array
    {
        return [static::STRATEGY_SOURCE_TREE];
    }

    public function getSources(): array
    {
        return [static::SOURCE_NAME];
    }

    public function extract(string $source, string $locale): ?MessageCatalogueInterface
    {
        if (!$this->isSourceAvailable($source)) {
            return null;
        }

        $fs = new Filesystem();

        /** @var Bundle $foundBundle */
        $foundBundle = $this->kernel->getBundle($this->bundle);

        // load any messages from templates
        $extractedCatalogue = new MessageCatalogue($locale);
        $resourcesDir = $this->resolveResourcesDirectory($foundBundle);
        if ($fs->exists($resourcesDir)) {
            $this->extractor->extract($resourcesDir, $extractedCatalogue);
        }

        // load any existing messages from the translation files
        $translationsDir = $foundBundle->getPath().'/Resources/translations';
        if ($fs->exists($translationsDir)) {
            $currentCatalogue = new MessageCatalogue($locale);
            $this->loader->read($translationsDir, $currentCatalogue);

            // load fallback translations
            $parts = \explode('_', $locale);
            if (\count($parts) > 1) {
                $fallbackCatalogue = new MessageCatalogue($parts[0]);
                $this->loader->read($translationsDir, $fallbackCatalogue);

                $intlMessages = [];
                $fallbackMessages = $fallbackCatalogue->all();
                foreach ($fallbackMessages as $domain => $messages) {
                    $arr = [];
                    foreach ($messages as $token => $translation) {
                        if ($fallbackCatalogue->defines($token, $domain.MessageCatalogue::INTL_DOMAIN_SUFFIX)) {
                            if (!isset($intlMessages[$domain.MessageCatalogue::INTL_DOMAIN_SUFFIX])) {
                                $intlMessages[$domain.MessageCatalogue::INTL_DOMAIN_SUFFIX] = [];
                            }
                            $intlMessages[$domain.MessageCatalogue::INTL_DOMAIN_SUFFIX][$token] = $translation;
                        } else {
                            $arr[$token] = $translation;
                        }
                    }
                    $fallbackMessages[$domain] = $arr;
                }

                $mergeOperation = new MergeOperation(
                    $currentCatalogue,
                    new MessageCatalogue($locale, \array_merge($fallbackMessages, $intlMessages))
                );
                $currentCatalogue = $mergeOperation->getResult();
            }

            foreach ($extractedCatalogue->getDomains() as $domain) {
                $messages = $currentCatalogue->all($domain);
                if (\count($messages)) {
                    $extractedCatalogue->add($messages, $domain);
                }
                $intlMessages = $currentCatalogue->all($domain.MessageCatalogue::INTL_DOMAIN_SUFFIX);
                if (\count($intlMessages)) {
                    $extractedCatalogue->add($intlMessages, $domain.MessageCatalogue::INTL_DOMAIN_SUFFIX);
                }
            }
        }

        return $extractedCatalogue;
    }

    protected function isSourceAvailable(string $source): bool
    {
        return static::SOURCE_NAME === $source;
    }

    protected function resolveResourcesDirectory(BundleInterface $bundle): string
    {
        return $bundle->getPath().'/Resources/views/';
    }
}

<?php

namespace Modera\TranslationsBundle\Handling;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\Translation\Reader\TranslationReader;
use Symfony\Component\Translation\Catalogue\MergeOperation;
use Symfony\Component\Translation\Extractor\ExtractorInterface;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class TemplateTranslationHandler implements TranslationHandlerInterface
{
    const SOURCE_NAME = 'template';

    /**
     * @var string
     */
    protected $bundle;

    /**
     * @var KernelInterface
     */
    protected $kernel;

    /**
     * @var ExtractorInterface
     */
    protected $extractor;

    /**
     * @var TranslationReader
     */
    protected $loader;

    /**
     * @param KernelInterface $kernel
     * @param TranslationReader $loader
     * @param ExtractorInterface $extractor
     * @param string $bundle
     */
    public function __construct(
        KernelInterface $kernel,
        TranslationReader $loader,
        ExtractorInterface $extractor,
        $bundle
    )
    {
        $this->kernel = $kernel;
        $this->loader = $loader;
        $this->extractor = $extractor;
        $this->bundle = $bundle;
    }

    /**
     * {@inheritdoc}
     */
    public function getBundleName()
    {
        return $this->bundle;
    }

    /**
     * {@inheritdoc}
     */
    public function getSources()
    {
        return array(static::SOURCE_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function extract($source, $locale)
    {
        if (!$this->isSourceAvailable($source)) {
            return;
        }

        $fs = new Filesystem();

        /* @var Bundle $foundBundle */
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
            $parts = explode('_', $locale);
            if (count($parts) > 1) {
                $fallbackCatalogue = new MessageCatalogue($parts[0]);
                $this->loader->read($translationsDir, $fallbackCatalogue);

                $mergeOperation = new MergeOperation(
                    $currentCatalogue,
                    new MessageCatalogue($locale, $fallbackCatalogue->all())
                );
                $currentCatalogue = $mergeOperation->getResult();
            }

            foreach ($extractedCatalogue->getDomains() as $domain) {
                $messages = $currentCatalogue->all($domain);
                if (count($messages)) {
                    $extractedCatalogue->add($messages, $domain);
                }
            }
        }

        return $extractedCatalogue;
    }

    /**
     * @param string $source
     *
     * @return bool
     */
    protected function isSourceAvailable($source)
    {
        return static::SOURCE_NAME == $source;
    }

    /**
     * @param BundleInterface $bundle
     *
     * @return string
     */
    protected function resolveResourcesDirectory(BundleInterface $bundle)
    {
        return $bundle->getPath().'/Resources/views/';
    }
}

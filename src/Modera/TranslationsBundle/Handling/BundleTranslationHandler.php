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
use Symfony\Component\Translation\Reader\TranslationReader;

abstract class BundleTranslationHandler implements TranslationHandlerInterface
{
    const SOURCE_NAME = 'bundle';

    static $extractedCatalogues;

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
     * @return string
     */
    public function getBundleName()
    {
        return $this->bundle;
    }

    /**
     * @return array
     */
    public function getSources()
    {
        return array(static::SOURCE_NAME);
    }

    /**
     * @param string $source
     * @param string $locale
     *
     * @return MessageCatalogueInterface | null
     */
    public function extract($source, $locale)
    {
        if (!$this->isSourceAvailable($source)) {
            return;
        }

        $fs = new Filesystem();

        /* @var Bundle $foundBundle */
        $foundBundle = $this->kernel->getBundle($this->bundle);
        $resourcesDir = $this->resolveResourcesDirectory($foundBundle);

        $cacheKey = $this->bundle . '||' . static::SOURCE_NAME;

        if ($this instanceof LocaleDependentTranslationHandlerInterface
            || (!($this instanceof LocaleDependentTranslationHandlerInterface) && !isset(static::$extractedCatalogues[$cacheKey]))) {

            // load any messages from templates
            $extractedCatalogue = new MessageCatalogue($locale);
            if ($fs->exists($resourcesDir)) {
                $this->extractor->extract($resourcesDir, $extractedCatalogue);
            }

            if (!$this instanceof LocaleDependentTranslationHandlerInterface) {
                static::$extractedCatalogues[$cacheKey] = $extractedCatalogue->all();
            }
        } else {
            $extractedCatalogue = new MessageCatalogue($locale, static::$extractedCatalogues[$cacheKey]);
        }

        return $extractedCatalogue;
    }

    /**
     * @param $source
     *
     * @return bool
     */
    protected function isSourceAvailable($source)
    {
        return static::SOURCE_NAME == $source;
    }

    /**
     * @param Bundle $bundle
     *
     * @return string
     */
    abstract protected function resolveResourcesDirectory(BundleInterface $bundle);
}
<?php

namespace Modera\BackendTranslationsToolBundle\Handling;

use Modera\BackendTranslationsToolBundle\Extractor\ExtjsExtractor;
use Modera\TranslationsBundle\Handling\TemplateTranslationHandler;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Translation\Reader\TranslationReaderInterface;

/**
 * @copyright 2014 Modera Foundation
 */
class ExtjsTranslationHandler extends TemplateTranslationHandler
{
    public const SOURCE_NAME = 'extjs';

    protected ?string $resourcesDirectory = null;

    public function __construct(
        KernelInterface $kernel,
        TranslationReaderInterface $loader,
        ExtjsExtractor $extractor,
        string $bundle,
    ) {
        parent::__construct($kernel, $loader, $extractor, $bundle);
    }

    public function setResourcesDirectory(?string $resourcesDirectory): void
    {
        $this->resourcesDirectory = $resourcesDirectory;
    }

    protected function resolveResourcesDirectory(BundleInterface $bundle): string
    {
        return $this->resourcesDirectory ?: $bundle->getPath().'/Resources/public/js/';
    }
}

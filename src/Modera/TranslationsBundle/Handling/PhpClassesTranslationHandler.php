<?php

namespace Modera\TranslationsBundle\Handling;

use Modera\TranslationsBundle\TokenExtraction\PhpClassTokenExtractor;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Translation\Reader\TranslationReaderInterface;

/**
 * @copyright 2014 Modera Foundation
 */
class PhpClassesTranslationHandler extends TemplateTranslationHandler
{
    public const SOURCE_NAME = 'php-classes';

    public function __construct(
        KernelInterface $kernel,
        TranslationReaderInterface $loader,
        PhpClassTokenExtractor $extractor,
        string $bundle,
    ) {
        parent::__construct($kernel, $loader, $extractor, $bundle);
    }

    protected function resolveResourcesDirectory(BundleInterface $bundle): string
    {
        return $bundle->getPath();
    }
}

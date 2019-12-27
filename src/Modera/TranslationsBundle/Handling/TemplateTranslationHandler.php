<?php

namespace Modera\TranslationsBundle\Handling;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\Translation\Reader\TranslationReader;
use Symfony\Component\Translation\Catalogue\MergeOperation;
use Symfony\Component\Translation\MessageCatalogueInterface;
use Symfony\Component\Translation\Extractor\ExtractorInterface;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class TemplateTranslationHandler extends BundleTranslationHandler
{
    const SOURCE_NAME = 'template';

    /**
     * @inheritDoc
     */
    protected function resolveResourcesDirectory(BundleInterface $bundle)
    {
        return $bundle->getPath() . '/Resources/views/';
    }
}

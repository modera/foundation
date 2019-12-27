<?php

namespace Modera\BackendTranslationsToolBundle\Handling;

use Modera\TranslationsBundle\Handling\BundleTranslationHandler;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class ExtjsTranslationHandler extends BundleTranslationHandler
{
    const SOURCE_NAME = 'extjs';

    /**
     * {@inheritdoc}
     */
    protected function resolveResourcesDirectory(BundleInterface $bundle)
    {
        return $bundle->getPath().'/Resources/public/js/';
    }
}

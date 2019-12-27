<?php

namespace Modera\TranslationsBundle\Handling;

use Symfony\Component\HttpKernel\Bundle\BundleInterface;

/**
 * @author Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class PhpClassesTranslationHandler extends BundleTranslationHandler
{
    const SOURCE_NAME = 'php-classes';

    /**
     * {@inheritdoc}
     */
    protected function resolveResourcesDirectory(BundleInterface $bundle)
    {
        return $bundle->getPath();
    }
}

<?php

namespace Modera\BackendTranslationsToolBundle\Handling;

use Modera\TranslationsBundle\Handling\TemplateTranslationHandler;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class ExtjsTranslationHandler extends TemplateTranslationHandler
{
    const SOURCE_NAME = 'extjs';

    /**
     * @var string
     */
    protected $resourcesDirectory;

    /**
     * @param string $resourcesDirectory
     */
    public function setResourcesDirectory($resourcesDirectory)
    {
        $this->resourcesDirectory = $resourcesDirectory;
    }

    /**
     * {@inheritdoc}
     */
    protected function resolveResourcesDirectory(BundleInterface $bundle)
    {
        return $this->resourcesDirectory ?: $bundle->getPath() . '/Resources/public/js/';
    }
}

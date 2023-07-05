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
    public const SOURCE_NAME = 'extjs';

    protected ?string $resourcesDirectory = null;

    public function setResourcesDirectory(?string $resourcesDirectory)
    {
        $this->resourcesDirectory = $resourcesDirectory;
    }

    /**
     * {@inheritdoc}
     */
    protected function resolveResourcesDirectory(BundleInterface $bundle): string
    {
        return $this->resourcesDirectory ?: $bundle->getPath() . '/Resources/public/js/';
    }
}

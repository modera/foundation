<?php

namespace Modera\TranslationsBundle\Handling;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\Translation\Catalogue\MergeOperation;
use Symfony\Component\Translation\MessageCatalogue;

class ResourcesFileTranslationHandler extends BundleTranslationHandler implements LocaleDependentTranslationHandlerInterface
{
    const SOURCE_NAME = 'resource';

    /**
     * @inheritDoc
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
        $translationsDir = $this->resolveResourcesDirectory($foundBundle);

        // load any existing messages from the translation files
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

            foreach ($currentCatalogue->getDomains() as $domain) {
                $messages = $currentCatalogue->all($domain);
                if (count($messages)) {
                    $extractedCatalogue->add($messages, $domain);
                }
            }
        }

        return $extractedCatalogue;
    }

    /**
     * @inheritDoc
     */
    protected function resolveResourcesDirectory(BundleInterface $bundle)
    {
        return $bundle->getPath() . '/Resources/translations/';
    }

}
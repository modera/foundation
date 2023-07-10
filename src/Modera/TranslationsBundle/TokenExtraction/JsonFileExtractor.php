<?php

namespace Modera\TranslationsBundle\TokenExtraction;

use Symfony\Component\Translation\Extractor\ExtractorInterface;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2023 Modera Foundation
 */
class JsonFileExtractor implements ExtractorInterface
{
    private string $locale = 'en';

    private string $prefix = '';

    public function __construct(
        ?string $locale = null
    ) {
        $this->locale = $locale ?? 'en';
    }

    /**
     * {@inheritdoc}
     */
    public function setPrefix(string $prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * {@inheritdoc}
     */
    public function extract($resource, MessageCatalogue $catalogue)
    {
        if (!\is_iterable($resource)) {
            $resource = array($resource);
        }

        foreach ($resource as $path) {
            $separator = '.';
            $filename = \basename($path);

            $parts = \explode($separator, $filename);
            if ('json' !== \array_pop($parts)) {
                continue;
            }

            $locale = \array_pop($parts);
            if ($locale !== $this->locale) {
                continue;
            }

            $domain = \implode($separator, $parts);

            $content = @\file_get_contents($path);
            if (false !== $content) {
                $messages = \json_decode($content, true);
                if (\is_array($messages)) {
                    foreach ($messages as $token => $translation) {
                        $catalogue->set($token, $this->prefix . $translation, $domain);
                    }
                }
            }
        }
    }
}

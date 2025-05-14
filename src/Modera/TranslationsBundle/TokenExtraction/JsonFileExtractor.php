<?php

namespace Modera\TranslationsBundle\TokenExtraction;

use Symfony\Component\Translation\Extractor\ExtractorInterface;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * @copyright 2023 Modera Foundation
 */
class JsonFileExtractor implements ExtractorInterface
{
    private string $prefix = '';

    public function __construct(
        private readonly string $locale = 'en',
    ) {
    }

    public function setPrefix(string $prefix): void
    {
        $this->prefix = $prefix;
    }

    public function extract($resource, MessageCatalogue $catalogue): void
    {
        if (!\is_iterable($resource)) {
            $resource = [$resource];
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
                        if (!\is_string($token) || !\is_string($translation)) {
                            continue;
                        }
                        $catalogue->set($token, $this->prefix.$translation, $domain);
                    }
                }
            }
        }
    }
}

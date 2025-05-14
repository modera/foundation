<?php

namespace Modera\BackendTranslationsToolBundle\FileProvider;

/**
 * Adds additional layer of indirection between {@class Modera\BackendTranslationsToolBundle\Extractor\ExtjsClassesExtractor}
 * and the files that it will need to scan, this thing will prove useful if you have lots of your own javascript
 * files as well as some vendor libraries. Most of the time you will want to extract tokens from your own javascript
 * files and ignore vendor related ones.
 */
interface FileProviderInterface
{
    /**
     * @param string $directory Base directory where to look at
     *
     * @return string[] Full paths to files that must be parsed by
     *                  {@class Modera\BackendTranslationsToolBundle\Extractor\ExtjsClassesExtractor}
     */
    public function getFiles(string $directory): array;
}

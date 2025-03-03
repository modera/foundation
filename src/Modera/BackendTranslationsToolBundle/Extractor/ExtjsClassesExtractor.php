<?php

namespace Modera\BackendTranslationsToolBundle\Extractor;

use Modera\BackendTranslationsToolBundle\FileProvider\ExtjsClassesProvider;
use Modera\BackendTranslationsToolBundle\FileProvider\FileProviderInterface;
use Symfony\Component\Translation\Extractor\ExtractorInterface;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * @author Sergei Lissovski <sergei.lissovski@modera.org>
 */
class ExtjsClassesExtractor implements ExtractorInterface
{
    public const DOMAIN = 'extjs';

    private string $prefix = '';

    private FileProviderInterface $pathProvider;

    public function __construct(?FileProviderInterface $pathProvider = null)
    {
        $this->pathProvider = null === $pathProvider ? new ExtjsClassesProvider() : $pathProvider;
    }

    public function getPathProvider(): FileProviderInterface
    {
        return $this->pathProvider;
    }

    public function setPrefix(string $prefix): void
    {
        $this->prefix = $prefix;
    }

    public function extract($resource, MessageCatalogue $catalogue): void
    {
        if (\is_array($resource)) {
            foreach ($resource as $dir) {
                $this->extract($dir, $catalogue);
            }
        } elseif (\is_string($resource)) {
            foreach ($this->pathProvider->getFiles($resource) as $filename) {
                foreach ($this->extractTokens($filename) as $token => $translation) {
                    $catalogue->set($token, $this->prefix.$translation, static::DOMAIN);
                }
            }
        }
    }

    /**
     * @return string[]
     */
    private function extractTokens(string $filename): array
    {
        $sourceCode = \file_get_contents($filename) ?: '';

        $isStartMarker = '@.*//\s*l10n.*@';

        $className = null;
        $tokensStartPosition = null;
        $tokensEndPosition = null;

        $lines = \explode("\n", $sourceCode);
        foreach ($lines as $i => $line) {
            if (null !== ExtjsClassesProvider::extractExtJsClassName($line)) {
                $className = ExtjsClassesProvider::extractExtJsClassName($line);
                continue;
            }

            if (\preg_match($isStartMarker, $line)) {
                $tokensStartPosition = $i + 1; // array index starts from 0, so we need a next line after the comment
                continue;
            }

            if ('' === \trim($line) && null === $tokensEndPosition && null !== $tokensStartPosition) {
                $tokensEndPosition = $i;
            }
        }

        if (null === $tokensStartPosition || null === $tokensEndPosition || null === $className) {
            return [];
        }

        $tokenLines = \array_slice($lines, $tokensStartPosition, $tokensEndPosition - $tokensStartPosition);

        $tokens = [];
        foreach ($tokenLines as $line) {
            $exp = \explode(':', $line, 2);  // split by  first ":" only with limit=2

            $token = \trim($exp[0]);
            $value = \trim($exp[1]); // getting rid of white spaces

            // getting rid of coma
            if (',' === $value[\strlen($value) - 1]) {
                $value = \substr($value, 0, \strlen($value) - 1);
            }

            // getting rid of wrapping " '
            if ($this->isStringWrappedBy($value, "'")) {
                $value = \trim($value, "'");
                $value = \str_replace("\'", "'", $value);
            } elseif ($this->isStringWrappedBy($value, '"')) {
                $value = \trim($value, '"');
                $value = \str_replace('\"', '"', $value);
            } else {
                continue;
            }

            if (\strlen($token) < 4) {
                $msg = \implode('', [
                    'The token "%s" must be at least 4 characters long. ',
                    'File path: "%s"',
                ]);
                throw new \RuntimeException(\sprintf($msg, $token, $filename));
            } elseif ('Text' !== \substr($token, -4, 4)) {
                $msg = \implode('', [
                    'The token "%s" must have "Text" suffix at the end. ',
                    'File path: "%s"',
                ]);
                throw new \RuntimeException(\sprintf($msg, $token, $filename));
            }
            $token = \substr($token, 0, -4); // removing "Text" suffix
            $token = $className.'.'.$token;

            $tokens[$token] = $value;
        }

        return $tokens;
    }

    private function isStringWrappedBy(string $string, string $wrap): bool
    {
        return $string[0] == $wrap && $string[\strlen($string) - 1] == $wrap;
    }
}

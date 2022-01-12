<?php

namespace Modera\BackendTranslationsToolBundle\Extractor;

use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Extractor\ExtractorInterface;
use Modera\BackendTranslationsToolBundle\FileProvider\FileProviderInterface;
use Modera\BackendTranslationsToolBundle\FileProvider\ExtjsClassesProvider;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */
class ExtjsClassesExtractor implements ExtractorInterface
{
    private $prefix;
    private $pathProvider;

    public function __construct(FileProviderInterface $pathProvider = null)
    {
        $this->pathProvider = null === $pathProvider ? new ExtjsClassesProvider() : $pathProvider;
    }

    /**
     * @return FileProviderInterface
     */
    public function getPathProvider()
    {
        return $this->pathProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * {@inheritdoc}
     */
    public function extract($directory, MessageCatalogue $catalogue)
    {
        foreach ($this->pathProvider->getFiles($directory) as $filename) {
            foreach ($this->extractTokens($filename) as $token=>$translation) {
                $catalogue->set($token, $this->prefix.$translation, 'extjs');
            }
        }
    }

    /**
     * @throws \RuntimeException
     * @param string $filename
     * @return string[]
     */
    private function extractTokens($filename)
    {
        $sourceCode = file_get_contents($filename);

        $isStartMarker = '@.*//\s*l10n.*@';

        $className = null;
        $tokensStartPosition = null;
        $tokensEndPosition = null;

        $lines = explode("\n", $sourceCode);
        foreach ($lines as $i => $line) {
            if (ExtjsClassesProvider::isValidExtjsClass($line)) {
                $className = ExtjsClassesProvider::isValidExtjsClass($line);
                continue;
            }

            if (preg_match($isStartMarker, $line)) {
                $tokensStartPosition = $i+1; // array index starts from 0 and we need a next line after the  comment
                continue;
            }

            if (trim($line) == '' && null === $tokensEndPosition && null !== $tokensStartPosition) {
                $tokensEndPosition = $i;
            }
        }

        if (null === $tokensStartPosition || null === $tokensEndPosition || null === $className) {
            return array();
        }

        $tokenLines = array_slice($lines, $tokensStartPosition, $tokensEndPosition - $tokensStartPosition);

        $tokens = array();
        foreach ($tokenLines as $line) {
            $exp = explode(':', $line, 2);  // split by  first ":" only with limit=2

            $token = trim($exp[0]);
            $value = trim($exp[1]); // getting rid of white spaces

            // getting rid of coma
            if ($value[strlen($value)-1] == ',') {
                $value = substr($value, 0, strlen($value)-1);
            }

            // getting rid of wrapping " '
            if ($this->isStringWrappedBy($value, "'")) {
                $value = trim($value, "'");
            } else if ($this->isStringWrappedBy($value, '"')) {
                $value = trim($value, '"');
            } else {
                continue;
            }

            if (strlen($token) < 4) {
                $msg = implode('', array(
                    'The token "%s" must be at least 4 characters long. ',
                    'File path: "%s"'
                ));
                throw new \RuntimeException(sprintf($msg, $token, $filename));
            } else if (substr($token, -4, 4) !== 'Text') {
                $msg = implode('', array(
                    'The token "%s" must have "Text" suffix at the end. ',
                    'File path: "%s"'
                ));
                throw new \RuntimeException(sprintf($msg, $token, $filename));
            }
            $token = substr($token, 0, -4); // removing "Text" suffix
            $token = $className.'.'.$token;

            $tokens[$token] = $value;
        }

        return $tokens;
    }

    private function isStringWrappedBy($string, $wrap)
    {
        return $string[0] == $wrap && $string[strlen($string)-1] == $wrap;
    }
}

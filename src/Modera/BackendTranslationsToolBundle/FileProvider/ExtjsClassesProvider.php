<?php

namespace Modera\BackendTranslationsToolBundle\FileProvider;

use Symfony\Component\Finder\Finder;

/**
 * @author Sergei Lissovski <sergei.lissovski@modera.org>
 */
class ExtjsClassesProvider implements FileProviderInterface
{
    private static ?string $regex = null;

    public static function extractExtJsClassName(string $sourceCode): ?string
    {
        if (!self::$regex) {
            self::$regex = \implode('', [ // FIXME stupid one
                '@',
                'Ext\.define\(',
                '(\'|")+',
                '(?P<className>.*)',
                '(\'|")+',
                '.*',
                ',',
                '@',
            ]);
        }

        \preg_match(self::$regex, $sourceCode, $matches);

        return $matches['className'] ?? null;
    }

    public function getFiles(string $directory): array
    {
        $paths = [];

        $finder = new Finder();
        foreach ($finder->files()->name('*.js')->in($directory) as $filepath) {
            $className = self::extractExtJsClassName(\file_get_contents($filepath) ?: '');
            if (null !== $className) {
                $paths[] = $filepath;
            }
        }

        return $paths;
    }
}

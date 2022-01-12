<?php

namespace Modera\BackendTranslationsToolBundle\FileProvider;

use Symfony\Component\Finder\Finder;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */
class ExtjsClassesProvider implements FileProviderInterface
{
    static private $regex;

    static public function isValidExtjsClass($sourceCode)
    {
        if (!self::$regex) {
            self::$regex = implode('', array( // FIXME stupid one
                '@',
                'Ext\.define\(',
                '(\'|")+',
                '(?P<className>.*)',
                '(\'|")+',
                '.*',
                ',',
                '@'
            ));
        }

        preg_match(self::$regex, $sourceCode, $matches);
        return isset($matches['className']) ? $matches['className'] : false;
    }

    static public function clazz()
    {
        return get_called_class();
    }

    /**
     * {@inheritDoc}
     */
    public function getFiles($directory)
    {
        $paths = array();

        $finder = new Finder();
        foreach ($finder->files()->name('*.js')->in($directory) as $filepath) {
            if (self::isValidExtjsClass(file_get_contents($filepath))) {
                $paths[] = $filepath;
            }
        }

        return $paths;
    }
}

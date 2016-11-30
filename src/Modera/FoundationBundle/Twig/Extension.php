<?php

namespace Modera\FoundationBundle\Twig;

/**
 * Base twig extensions used throughout the foundation.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2013 Modera Foundation
 */
final class Extension extends \Twig_Extension
{
    const NAME = 'modera-foundation-extension';

    /**
     * @var string
     */
    private $kernelPath;

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('mf_prepend_every_line', array($this, 'filter_prepend_every_line')),
            new \Twig_SimpleFilter('mf_modification_time', array($this, 'filter_modification_time')), // internal!
        );
    }

    /**
     * @internal
     *
     * Do not use this method! It is a temporary solution which is going to be removed at some point when high-level
     * API for managing assets is added to the platform.
     *
     * @param string $webPath
     *
     * @return string
     */
    public function filter_modification_time($webPath)
    {
        if (!$this->kernelPath) {
            $reflClass = new \ReflectionClass(\AppKernel::class);

            $this->kernelPath = dirname($reflClass->getFileName());
        }

        // because it is kind of convention already
        $webDir = $this->kernelPath.'/../web/';

        $assumedLocalPath = $webDir.$webPath;
        if (file_exists($webDir.$webPath)) {
            $mtime = filemtime($assumedLocalPath);

            // if server uses "expiration caching model" and we were unable to retrieve file's modification time
            // then every time filename is generate we are going to use current time to invalidate cache
            return $webPath.'?'.(false === $mtime ? time() : $mtime);
        }

        return $webPath;
    }

    /**
     * Prepends every line of given $input with $prefix $multiplier-times.
     *
     * @param string $input
     * @param string $multiplier
     * @param string $prefix
     * @param bool   $skipFirstLine
     *
     * @return string
     */
    public function filter_prepend_every_line($input, $multiplier, $prefix = ' ', $skipFirstLine = false)
    {
        $output = explode("\n", $input);

        foreach ($output as $i => &$line) {
            if ($skipFirstLine && 0 === $i) {
                continue;
            }

            $line = str_repeat($prefix, $multiplier).$line;
        }

        return implode("\n", $output);
    }
}

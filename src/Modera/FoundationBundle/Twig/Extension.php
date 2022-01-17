<?php

namespace Modera\FoundationBundle\Twig;

use Twig\TwigFilter;
use Twig\Extension\AbstractExtension;

/**
 * Base twig extensions used throughout the foundation.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2013 Modera Foundation
 */
final class Extension extends AbstractExtension
{
    private string $publicDir;

    public function __construct(string $publicDir)
    {
        $this->publicDir = $publicDir;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            new TwigFilter('mf_prepend_every_line', array($this, 'filter_prepend_every_line')),
            new TwigFilter('mf_modification_time', array($this, 'filter_modification_time')), // internal!
        );
    }

    /**
     * @internal
     *
     * Do not use this method! It is a temporary solution which is going to be removed at some point when high-level
     * API for managing assets is added to the platform.
     *
     * @param string $webPath  If URL is given then we won't check modification time
     *
     * @return string
     */
    public function filter_modification_time($webPath)
    {
        $assumedLocalPath = $this->publicDir . '/' . $webPath;

        if (@file_exists($assumedLocalPath)) {
            $mtime = filemtime($assumedLocalPath);

            // If server uses "expiration caching model" and we were unable to retrieve file's modification time
            // then every time filename is generated we are going to use current time to invalidate cache,
            // taking a safe side here
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

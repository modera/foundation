<?php

namespace Modera\FoundationBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

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

    public function getFilters(): array
    {
        return [
            new TwigFilter('mf_prepend_every_line', [$this, 'filter_prepend_every_line']),
            new TwigFilter('mf_modification_time', [$this, 'filter_modification_time']), // internal!
        ];
    }

    /**
     * @internal
     *
     * Do not use this method! It is a temporary solution which is going to be removed at some point when high-level
     * API for managing assets is added to the platform.
     *
     * If URL is given then we won't check modification time.
     */
    public function filter_modification_time(string $webPath): string
    {
        $assumedLocalPath = $this->publicDir.'/'.$webPath;

        if (@\file_exists($assumedLocalPath)) {
            $time = (string) (\filemtime($assumedLocalPath) ?: \time());

            // If server uses "expiration caching model" and we were unable to retrieve file's modification time
            // then every time filename is generated we are going to use current time to invalidate cache,
            // taking a safe side here
            return $webPath.'?'.$time;
        }

        return $webPath;
    }

    /**
     * Prepends every line of given $input with $prefix $multiplier-times.
     */
    public function filter_prepend_every_line(string $input, int $multiplier, string $prefix = ' ', bool $skipFirstLine = false): string
    {
        $output = \explode("\n", $input);

        foreach ($output as $i => &$line) {
            if ($skipFirstLine && 0 === $i) {
                continue;
            }

            $line = \str_repeat($prefix, $multiplier).$line;
        }

        return \implode("\n", $output);
    }
}

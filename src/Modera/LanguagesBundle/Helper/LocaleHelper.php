<?php

namespace Modera\LanguagesBundle\Helper;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2020 Modera Foundation
 */
class LocaleHelper
{
    public const RTL = ['ar', 'he'];

    /**
     * Retrieve the language direction (ltr or rtl).
     */
    public static function getDirection(string $locale): string
    {
        $needle = $locale;
        $parts = \explode('_', $locale);
        if (\count($parts) > 1) {
            $needle = $parts[0];
        }

        return \in_array($needle, static::RTL) ? 'rtl' : 'ltr';
    }
}

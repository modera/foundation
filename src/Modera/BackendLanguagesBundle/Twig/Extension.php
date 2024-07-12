<?php

namespace Modera\BackendLanguagesBundle\Twig;

use Modera\BackendLanguagesBundle\ExtUtilFormatResolving\ExtUtilFormatResolverInterface;
use Modera\BackendLanguagesBundle\Service\SanitizeInterface;
use Modera\ExpanderBundle\Ext\ContributorInterface;
use Symfony\Component\Intl\Locales;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class Extension extends AbstractExtension
{
    private const REGION_LANGUAGES = [
        'AD' => 'ca',
        'AE' => 'ar',
        'AF' => 'fa,ps',
        'AG' => 'en',
        'AI' => 'en',
        'AL' => 'sq',
        'AM' => 'hy',
        'AN' => 'nl,en',
        'AO' => 'pt',
        'AQ' => 'en',
        'AR' => 'es',
        'AS' => 'en,sm',
        'AT' => 'de',
        'AU' => 'en',
        'AW' => 'nl,pap',
        'AX' => 'sv',
        'AZ' => 'az',
        'BA' => 'bs,hr,sr',
        'BB' => 'en',
        'BD' => 'bn',
        'BE' => 'nl,fr,de',
        'BF' => 'fr',
        'BG' => 'bg',
        'BH' => 'ar',
        'BI' => 'fr',
        'BJ' => 'fr',
        'BL' => 'fr',
        'BM' => 'en',
        'BN' => 'ms',
        'BO' => 'es,qu,ay',
        'BR' => 'pt',
        'BQ' => 'nl,en',
        'BS' => 'en',
        'BT' => 'dz',
        'BV' => 'no',
        'BW' => 'en,tn',
        'BY' => 'be,ru',
        'BZ' => 'en',
        'CA' => 'en,fr',
        'CC' => 'en',
        'CD' => 'fr',
        'CF' => 'fr',
        'CG' => 'fr',
        'CH' => 'de,fr,it,rm',
        'CI' => 'fr',
        'CK' => 'en,rar',
        'CL' => 'es',
        'CM' => 'fr,en',
        'CN' => 'zh',
        'CO' => 'es',
        'CR' => 'es',
        'CU' => 'es',
        'CV' => 'pt',
        'CW' => 'nl',
        'CX' => 'en',
        'CY' => 'el,tr',
        'CZ' => 'cs',
        'DE' => 'de',
        'DJ' => 'fr,ar,so',
        'DK' => 'da',
        'DM' => 'en',
        'DO' => 'es',
        'DZ' => 'ar',
        'EC' => 'es',
        'EE' => 'et',
        'EG' => 'ar',
        'EH' => 'ar,es,fr',
        'ER' => 'ti,ar,en',
        'ES' => 'es,ast,ca,eu,gl',
        'ET' => 'am,om',
        'FI' => 'fi,sv,se',
        'FJ' => 'en',
        'FK' => 'en',
        'FM' => 'en',
        'FO' => 'fo',
        'FR' => 'fr',
        'GA' => 'fr',
        'GB' => 'en,ga,cy,gd,kw',
        'GD' => 'en',
        'GE' => 'ka',
        'GF' => 'fr',
        'GG' => 'en',
        'GH' => 'en',
        'GI' => 'en',
        'GL' => 'kl,da',
        'GM' => 'en',
        'GN' => 'fr',
        'GP' => 'fr',
        'GQ' => 'es,fr,pt',
        'GR' => 'el',
        'GS' => 'en',
        'GT' => 'es',
        'GU' => 'en,ch',
        'GW' => 'pt',
        'GY' => 'en',
        'HK' => 'zh,en',
        'HM' => 'en',
        'HN' => 'es',
        'HR' => 'hr',
        'HT' => 'fr,ht',
        'HU' => 'hu',
        'ID' => 'id',
        'IE' => 'en,ga',
        'IL' => 'he',
        'IM' => 'en',
        'IN' => 'hi,en',
        'IO' => 'en',
        'IQ' => 'ar,ku',
        'IR' => 'fa',
        'IS' => 'is',
        'IT' => 'it,de,fr',
        'JE' => 'en',
        'JM' => 'en',
        'JO' => 'ar',
        'JP' => 'ja',
        'KE' => 'sw,en',
        'KG' => 'ky,ru',
        'KH' => 'km',
        'KI' => 'en',
        'KM' => 'ar,fr',
        'KN' => 'en',
        'KP' => 'ko',
        'KR' => 'ko,en',
        'KW' => 'ar',
        'KY' => 'en',
        'KZ' => 'kk,ru',
        'LA' => 'lo',
        'LB' => 'ar,fr',
        'LC' => 'en',
        'LI' => 'de',
        'LK' => 'si,ta',
        'LR' => 'en',
        'LS' => 'en,st',
        'LT' => 'lt',
        'LU' => 'lb,fr,de',
        'LV' => 'lv',
        'LY' => 'ar',
        'MA' => 'ar',
        'MC' => 'fr',
        'MD' => 'ru,uk,ro',
        'ME' => 'srp,sq,bs,hr,sr',
        'MF' => 'fr',
        'MG' => 'mg,fr',
        'MH' => 'en,mh',
        'MK' => 'mk',
        'ML' => 'fr',
        'MM' => 'my',
        'MN' => 'mn',
        'MO' => 'zh,en,pt',
        'MP' => 'ch',
        'MQ' => 'fr',
        'MR' => 'ar,fr',
        'MS' => 'en',
        'MT' => 'mt,en',
        'MU' => 'mfe,fr,en',
        'MV' => 'dv',
        'MW' => 'en,ny',
        'MX' => 'es',
        'MY' => 'ms,zh,en',
        'MZ' => 'pt',
        'NA' => 'en,sf,de',
        'NC' => 'fr',
        'NE' => 'fr',
        'NF' => 'en,pih',
        'NG' => 'en',
        'NI' => 'es',
        'NL' => 'nl',
        'NO' => 'nb,nn,no,se',
        'NP' => 'ne',
        'NR' => 'na,en',
        'NU' => 'niu,en',
        'NZ' => 'en,mi',
        'OM' => 'ar',
        'PA' => 'es',
        'PE' => 'es',
        'PF' => 'fr',
        'PG' => 'en,tpi,ho',
        'PH' => 'en,tl',
        'PK' => 'en,ur',
        'PL' => 'pl',
        'PM' => 'fr',
        'PN' => 'en,pih',
        'PR' => 'es,en',
        'PS' => 'ar,he',
        'PT' => 'pt',
        'PW' => 'en,pau,ja,sov,tox',
        'PY' => 'es,gn',
        'QA' => 'ar',
        'RE' => 'fr',
        'RO' => 'ro',
        'RS' => 'sr',
        'RU' => 'ru',
        'RW' => 'rw,fr,en',
        'SA' => 'ar',
        'SB' => 'en',
        'SC' => 'fr,en,crs',
        'SD' => 'ar,en',
        'SE' => 'sv',
        'SG' => 'en,ms,zh,ta',
        'SH' => 'en',
        'SI' => 'sl',
        'SJ' => 'no',
        'SK' => 'sk',
        'SL' => 'en',
        'SM' => 'it',
        'SN' => 'fr',
        'SO' => 'so,ar',
        'SR' => 'nl',
        'ST' => 'pt',
        'SS' => 'en',
        'SV' => 'es',
        'SX' => 'nl,en',
        'SY' => 'ar',
        'SZ' => 'en,ss',
        'TC' => 'en',
        'TD' => 'fr,ar',
        'TF' => 'fr',
        'TG' => 'fr',
        'TH' => 'th',
        'TJ' => 'tg,ru',
        'TK' => 'tkl,en,sm',
        'TL' => 'pt,tet',
        'TM' => 'tk',
        'TN' => 'ar',
        'TO' => 'en',
        'TR' => 'tr',
        'TT' => 'en',
        'TV' => 'en',
        'TW' => 'zh',
        'TZ' => 'sw,en',
        'UA' => 'uk',
        'UG' => 'en,sw',
        'UM' => 'en',
        'US' => 'en,es',
        'UY' => 'es',
        'UZ' => 'uz,kaa',
        'VA' => 'it',
        'VC' => 'en',
        'VE' => 'es',
        'VG' => 'en',
        'VI' => 'en',
        'VN' => 'vi',
        'VU' => 'bi,en,fr',
        'WF' => 'fr',
        'WS' => 'sm,en',
        'YE' => 'ar',
        'YT' => 'fr',
        'ZA' => 'zu,xh,af,st,tn,en',
        'ZM' => 'en',
        'ZW' => 'en,sn,nd',
    ];

    private const CLDR_MAPPING = [
        // Era is not implemented
        'GGGGG' => '',
        'GGGG' => '',
        'GGG' => '',
        'GG' => '',
        'G' => '',

        // Year
        'yyyy' => 'Y', // 1999
        'yy' => 'y', // 99
        'y' => 'Y', // 1999

        // Month.
        'MMMM' => 'F',
        'MMM' => 'M',
        'MM' => 'm',
        'M' => 'm',

        // Day.
        'dd' => 'd',
        'd' => 'j',

        // Day of week.
        'EEEEEE' => '', // Tu
        'EEEEE' => '', // T
        'EEEE' => 'l', // Tuesday
        'EEE' => 'D', // Tue
        'EE' => 'D', // Tue
        'E' => 'D', // Tue

        // Am/PM
        'a' => 'a',

        // hours
        'HH' => 'H', // 24-hour format of an hour with leading zeros
        'H' => 'G', // 24-hour format of an hour without leading zeros
        'h' => 'h', // 12-hour format of an hour with leading zeros
        'K' => 'g', // 12-hour format of an hour without leading zero

        // minutes
        'mm' => 'i', // Minutes with leading zeros
        'ss' => 's', // Seconds, with leading zeros

        // timezone.
        'z' => 'T', // Timezone abbreviation
        'zz' => 'T', // Timezone abbreviation
        'zzz' => 'T', // Timezone abbreviation
        'zzzz' => 'e', // Timezone
    ];

    private ContributorInterface $customLocalesProvider;

    private ContributorInterface $extUtilFormatResolverProvider;

    private SanitizeInterface $sanitizationService;

    public function __construct(
        ContributorInterface $customLocalesProvider,
        ContributorInterface $extUtilFormatResolverProvider,
        SanitizeInterface $sanitizationService
    ) {
        $this->customLocalesProvider = $customLocalesProvider;
        $this->extUtilFormatResolverProvider = $extUtilFormatResolverProvider;
        $this->sanitizationService = $sanitizationService;
    }

    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'modera_backend_languages_escape',
                [$this, 'escapeJsString'],
                ['is_safe' => ['html', 'js'], 'needs_environment' => true]
            ),
        ];
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'modera_backend_languages_ext_util_format',
                [$this, 'getExtUtilFormat'],
                ['is_safe' => ['html', 'js']]
            ),
            new TwigFunction(
                'modera_backend_languages_ext_date_format',
                [$this, 'getExtDateFormat'],
                ['is_safe' => ['html', 'js']]
            ),
            new TwigFunction(
                'modera_backend_languages_ext_time_format',
                [$this, 'getExtTimeFormat'],
                ['is_safe' => ['html', 'js']]
            ),
            new TwigFunction(
                'modera_backend_languages_ext_start_day',
                [$this, 'getExtStartDay'],
                ['is_safe' => ['html', 'js']]
            ),
        ];
    }

    public function escapeJsString(Environment $env, string $string): string
    {
        $string = $this->sanitizationService->sanitizeHtml($string);

        return \twig_escape_filter($env, $string, 'js');
    }

    public function getExtUtilFormat(string $locale): string
    {
        $locales = [];
        foreach ($this->getLocales() as $value) {
            $locales[$value] = $this->getLocaleFormat($value);
        }
        $default = $locales[$locale] ?? $this->getLocaleFormat($locale);

        return \json_encode(\array_merge([
            '_default' => $default,
            '_locales' => $locales,
        ], $default)) ?: '{}';
    }

    public function getExtDateFormat(string $locale): string
    {
        return $this->getIntlDateFormatterPattern($locale, 'date');
    }

    public function getExtTimeFormat(string $locale): string
    {
        return $this->getIntlDateFormatterPattern($locale, 'time');
    }

    public function getExtStartDay(string $locale): int
    {
        $cal = \IntlCalendar::createInstance(null, $locale);

        return $cal->getFirstDayOfWeek() - 1;
    }

    /**
     * @return string[]
     */
    private function getLocales(): array
    {
        $locales = \array_keys(Locales::getNames());
        foreach ($this->getCustomLocales() as $locale) {
            $locales[] = $locale;
        }

        return $locales;
    }

    /**
     * @return string[]
     */
    private function getCustomLocales(): array
    {
        $locales = [];
        /** @var string $locale */
        foreach ($this->customLocalesProvider->getItems() as $locale) {
            $locales[] = $locale;
        }

        return $locales;
    }

    /**
     * @return string[]
     */
    private function getLanguagesByRegion(string $code): array
    {
        if (\array_key_exists($code, self::REGION_LANGUAGES)) {
            if (false !== \strpos(self::REGION_LANGUAGES[$code], ',')) {
                return \explode(',', self::REGION_LANGUAGES[$code]);
            } else {
                return [self::REGION_LANGUAGES[$code]];
            }
        }

        return [];
    }

    /**
     * @return array<string, mixed>
     */
    private function getLocaleFormat(string $locale): array
    {
        $arr = \Locale::parseLocale($locale) ?? [];

        if (isset($arr['region'])) {
            $languages = $this->getLanguagesByRegion($arr['region']);
            if (\count($languages)) {
                $arr['language'] = $languages[0];
            }
        }

        $fmt1 = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
        $fmt2 = new \NumberFormatter(\locale_compose($arr) ?: '', \NumberFormatter::CURRENCY);

        $currencySign = $fmt1->getSymbol(\NumberFormatter::CURRENCY_SYMBOL);
        $decimalSeparator = $fmt2->getSymbol(\NumberFormatter::DECIMAL_SEPARATOR_SYMBOL);
        $thousandSeparator = $fmt2->getSymbol(\NumberFormatter::GROUPING_SEPARATOR_SYMBOL);
        $currencyAtEnd = '' === \explode('€', \str_replace('EUR', '€', $fmt2->formatCurrency(0, 'EUR') ?: ''))[1];

        $config = [
            '_language' => $arr['language'],
            'thousandSeparator' => $thousandSeparator,
            'decimalSeparator' => $decimalSeparator,
            'currencySign' => $currencySign,
            'currencyAtEnd' => $currencyAtEnd,
        ];

        $items = $this->extUtilFormatResolverProvider->getItems();
        foreach ($items as $index => $resolver) {
            if ($resolver instanceof ExtUtilFormatResolverInterface) {
                $config = $resolver->resolveExtUtilFormat($locale, $config);
            }
        }

        return $config;
    }

    private function getIntlDateFormatterPattern(string $locale, string $type = 'date'): string
    {
        $arr = \Locale::parseLocale($locale) ?? [];

        if (isset($arr['region'])) {
            $languages = $this->getLanguagesByRegion($arr['region']);
            if (\count($languages)) {
                $arr['language'] = $languages[0];
            }
        }

        if ('time' == $type) {
            $fmt = new \IntlDateFormatter(\locale_compose($arr) ?: null, \IntlDateFormatter::NONE, \IntlDateFormatter::SHORT);
        } else {
            $fmt = new \IntlDateFormatter(\locale_compose($arr) ?: null, \IntlDateFormatter::SHORT, \IntlDateFormatter::NONE);
        }

        return $this->convertCLDRtoPHP($fmt->getPattern() ?: '');
    }

    private function convertCLDRtoPHP(string $value): string
    {
        $mapping = self::CLDR_MAPPING;
        $splitters = [', ', ' ', ',', '-', '\/', '\\.', '\'', ':'];
        /** @var string[] $array */
        $array = \preg_split('/('.\implode('|', $splitters).')/', $value);
        \usort($array, function ($a, $b) {
            return \strlen($b) - \strlen($a);
        });

        foreach ($array as $search) {
            $replace = isset($mapping[$search]) ? $mapping[$search] : '*';
            $value = \str_replace($search, $replace, $value);
        }

        return \str_replace(' ,', ',', $value);
    }
}

<?php

namespace Modera\BackendLanguagesBundle\Twig;

use Symfony\Component\Intl\Intl;
use Sli\ExpanderBundle\Ext\ContributorInterface;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class Extension extends \Twig_Extension
{
    const REGION_LANGUAGES = array(
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
    );

    const CLDR_MAPPING = array(
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
    );

    /**
     * @var ContributorInterface
     */
    private $customLocalesProvider;

    /**
     * @param ContributorInterface $customLocalesProvider
     */
    public function __construct(ContributorInterface $customLocalesProvider)
    {
        $this->customLocalesProvider = $customLocalesProvider;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction(
                'modera_backend_languages_ext_util_format',
                array($this, 'getExtUtilFormat'),
                array('is_safe' => array('html', 'js'))
            ),
            new \Twig_SimpleFunction(
                'modera_backend_languages_ext_date_format',
                array($this, 'getExtDateFormat'),
                array('is_safe' => array('html', 'js'))
            ),
            new \Twig_SimpleFunction(
                'modera_backend_languages_ext_time_format',
                array($this, 'getExtTimeFormat'),
                array('is_safe' => array('html', 'js'))
            ),
            new \Twig_SimpleFunction(
                'modera_backend_languages_ext_start_day',
                array($this, 'getExtStartDay'),
                array('is_safe' => array('html', 'js'))
            ),
        );
    }

    /**
     * @return string
     */
    public function getExtUtilFormat($locale)
    {
        $locales = array();
        foreach ($this->getLocales() as $value) {
            $locales[$value] = $this->getLocaleFormat($value);
        }
        $default = isset($locales[$locale]) ? $locales[$locale] : $this->getLocaleFormat($locale);

        return json_encode(array_merge(array(
            '_default' => $default,
            '_locales' => $locales,
        ), $default));
    }

    /**
     * @param string $locale
     * @return string
     */
    public function getExtDateFormat($locale)
    {
        return $this->getIntlDateFormatterPattern($locale, 'date');
    }

    /**
     * @param string $locale
     * @return string
     */
    public function getExtTimeFormat($locale)
    {
        return $this->getIntlDateFormatterPattern($locale, 'time');
    }

    /**
     * @param string $locale
     * @return string
     */
    public function getExtStartDay($locale)
    {
        $cal = \IntlCalendar::createInstance(NULL, $locale);
        return $cal->getFirstDayOfWeek() - 1;
    }

    /**
     * @return array
     */
    private function getLocales()
    {
        $locales = array_keys(Intl::getLocaleBundle()->getLocaleNames());
        foreach ($this->getCustomLocales() as $locale) {
            $locales[] = $locale;
        }
        return $locales;
    }

    /**
     * @return array
     */
    private function getCustomLocales()
    {
        $locales = array();
        foreach ($this->customLocalesProvider->getItems() as $locale) {
            $locales[] = $locale;
        }
        return $locales;
    }

    /**
     * @param string $code
     * @return array
     */
    private function getLanguagesByRegion($code)
    {
        if (array_key_exists($code, static::REGION_LANGUAGES)) {
            if (strpos(static::REGION_LANGUAGES[$code], ',') !== false) {
                return explode(',', static::REGION_LANGUAGES[$code]);
            } else {
                return array(static::REGION_LANGUAGES[$code]);
            }
        }
        return array();
    }

    /**
     * @param string $locale
     * @return array
     */
    private function getLocaleFormat($locale)
    {
        $arr = \Locale::parseLocale($locale);

        if (isset($arr['region'])) {
            $languages = $this->getLanguagesByRegion($arr['region']);
            if (count($languages)) {
                $arr['language'] = $languages[0];
            }
        }

        $fmt1 = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
        $fmt2 = new \NumberFormatter(locale_compose($arr), \NumberFormatter::CURRENCY);

        $currencySign = $fmt1->getSymbol(\NumberFormatter::CURRENCY_SYMBOL);
        $decimalSeparator = $fmt2->getSymbol(\NumberFormatter::DECIMAL_SEPARATOR_SYMBOL);
        $thousandSeparator = $fmt2->getSymbol(\NumberFormatter::GROUPING_SEPARATOR_SYMBOL);
        $currencyAtEnd = explode('€', str_replace('EUR', '€', $fmt2->formatCurrency(0, 'EUR')))[1] == '';

        return array(
            '_language' => $arr['language'],
            'thousandSeparator' => $thousandSeparator,
            'decimalSeparator' => $decimalSeparator,
            'currencySign' => $currencySign,
            'currencyAtEnd' => $currencyAtEnd,
        );
    }

    /**
     * @param string $locale
     * @param string $type
     * @return string
     */
    private function getIntlDateFormatterPattern($locale, $type = 'date')
    {
        $arr = \Locale::parseLocale($locale);

        if (isset($arr['region'])) {
            $languages = $this->getLanguagesByRegion($arr['region']);
            if (count($languages)) {
                $arr['language'] = $languages[0];
            }
        }

        if ('time' == $type) {
            $fmt = new \IntlDateFormatter(locale_compose($arr), \IntlDateFormatter::NONE, \IntlDateFormatter::SHORT);
        } else {
            $fmt = new \IntlDateFormatter(locale_compose($arr), \IntlDateFormatter::SHORT, \IntlDateFormatter::NONE);
        }

        return $this->convertCLDRtoPHP($fmt->getPattern());
    }

    /**
     * @param string $value
     * @return string
     */
    private function convertCLDRtoPHP($value)
    {
        $mapping = static::CLDR_MAPPING;
        $splitters = array(', ', ' ', ',', '-', '\/', '\\.', '\'', ':');
        $array = preg_split('/(' . implode('|', $splitters) . ')/',  $value);
        usort($array, function($a, $b){
            return strlen($b) - strlen($a);
        });

        foreach ($array as $search) {
            $replace = isset($mapping[$search]) ? $mapping[$search] : '*';
            $value = str_replace($search, $replace, $value);
        }
        return str_replace(' ,', ',', $value);
    }
}

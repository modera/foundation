<?php

namespace Modera\MjrIntegrationBundle\Model;

use Symfony\Component\Yaml\Yaml;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class FontAwesome
{
    public static string $version = '5.15.4';

    public static string $cdn = 'https://use.fontawesome.com/releases/v';

    public static ?string $bucket = 'https://raw.githubusercontent.com/FortAwesome/Font-Awesome';

    public static string $iconsYml = '/metadata/icons.yml';

    public static string $shimsYml = '/metadata/shims.yml';

    public static bool $rejectUnauthorized = true;

    public static ?string $cacheDir = null;

    /**
     * @var ?array{
     *     'metadata': array{
     *         'icons': array<
     *             string,
     *             array{
     *                 'unicode': string,
     *                 'styles': string[],
     *                 'search'?: array{'terms'?: string[]}
     *             },
     *         >,
     *         'shims': array<string, array{'name'?: string, 'prefix'?: string}>,
     *     },
     *     'css': array{
     *         'brands': string,
     *         'light': false|string,
     *         'regular': string,
     *         'solid': string,
     *     },
     * }
     */
    private static ?array $cache = null;

    /**
     * @var array{
     *     'fas': string,
     *     'far': string,
     *     'fal': string,
     *     'fab': string,
     *     'solid': string,
     *     'regular': string,
     *     'light': string,
     *     'brands': string,
     * }
     */
    private static array $aliases = [
        'fas' => 'FontAwesomeSolid',
        'far' => 'FontAwesomeRegular',
        'fal' => 'FontAwesomeLight',
        'fab' => 'FontAwesomeBrands',

        'solid' => 'FontAwesomeSolid',
        'regular' => 'FontAwesomeRegular',
        'light' => 'FontAwesomeLight',
        'brands' => 'FontAwesomeBrands',
    ];

    /**
     * @return array{
     *     'metadata': array{
     *         'icons': array<
     *             string,
     *             array{
     *                 'unicode': string,
     *                 'styles': string[],
     *                 'search'?: array{'terms'?: string[]}
     *             },
     *         >,
     *         'shims': array<string, array{'name'?: string, 'prefix'?: string}>,
     *     },
     *     'css': array{
     *         'brands': string,
     *         'light': false|string,
     *         'regular': string,
     *         'solid': string,
     *     },
     * }
     */
    private static function getCache(): array
    {
        if (null !== self::$cache) {
            return self::$cache;
        }

        $cacheDir = self::$cacheDir ?: \dirname(__DIR__).'/Resources/cache/font-awesome';
        $cacheVersion = \substr(\md5(\join(';', [
            self::$cdn,
            self::$bucket,
            self::$iconsYml,
            self::$shimsYml,
        ])), 0, 5);
        $cachePathname = $cacheDir.'/'.self::$version.'-'.$cacheVersion.'.php';

        // create cache
        if (!\file_exists($cachePathname)) {
            if (!\file_exists($cacheDir)) {
                \mkdir($cacheDir, 0777, true);
            }

            $context = \stream_context_create([
                'ssl' => [
                    'verify_peer' => self::$rejectUnauthorized,
                    'verify_peer_name' => self::$rejectUnauthorized,
                ],
            ]);

            $iconsYml = self::$bucket.'/'.self::$version.self::$iconsYml;
            $shimsYml = self::$bucket.'/'.self::$version.self::$shimsYml;

            $metadata = [
                'icons' => Yaml::parse(\file_get_contents(self::$bucket ? $iconsYml : self::$iconsYml, false, $context) ?: ''),
                'shims' => Yaml::parse(\file_get_contents(self::$bucket ? $shimsYml : self::$shimsYml, false, $context) ?: ''),
            ];

            $css = [
                'brands' => \file_get_contents(self::$cdn.self::$version.'/css/brands.css', false, $context),
                'light' => @\file_get_contents(self::$cdn.self::$version.'/css/light.css', false, $context),
                'regular' => \file_get_contents(self::$cdn.self::$version.'/css/regular.css', false, $context),
                'solid' => \file_get_contents(self::$cdn.self::$version.'/css/solid.css', false, $context),
            ];

            \file_put_contents($cachePathname, \join(PHP_EOL, [
                '<?php',
                'return '.\var_export([
                    'metadata' => $metadata,
                    'css' => $css,
                ], true).';',
                '',
            ]));
        }

        self::$cache = require $cachePathname;

        return self::$cache;
    }

    /**
     * @return array{
     *     'icons': array<
     *         string,
     *         array{
     *             'unicode': string,
     *             'styles': string[],
     *             'search'?: array{'terms'?: string[]}
     *         },
     *     >,
     *     'shims': array<string, array{'name'?: string, 'prefix'?: string}>,
     * }
     */
    private static function getMetadata(): array
    {
        $cache = self::getCache();

        return $cache['metadata'];
    }

    /**
     * @return array{
     *     'brands': string,
     *     'light': false|string,
     *     'regular': string,
     *     'solid': string,
     * }
     */
    private static function getCss(): array
    {
        $cache = self::getCache();

        return $cache['css'];
    }

    private static function prepareIcon(string $unicode, string $fontFamily): string
    {
        return 'x'.$unicode.'@'.$fontFamily;
    }

    public static function unicode(string $value, ?string $style = null): string
    {
        return self::prepareIcon($value, $style ? self::$aliases[$style] : 'FontAwesome');
    }

    /**
     * http://fontawesome.io/icons/.
     */
    public static function resolve(string $name, ?string $style = null): ?string
    {
        if (\count(\explode('@', $name)) > 1) {
            return $name;
        }

        $fontFamily = null;
        $metadata = self::getMetadata();

        if (false !== \strrpos($name, 'fa-')) {
            $name = \substr($name, 3);
        }

        if (!$style) {
            if (isset($metadata['shims'][$name])) {
                $shim = $metadata['shims'][$name];

                if (isset($shim['name'])) {
                    $name = $shim['name'];
                }

                if (isset($shim['prefix']) && isset(self::$aliases[$shim['prefix']])) {
                    $fontFamily = self::$aliases[$shim['prefix']];
                }
            } elseif (!isset($metadata['icons'][$name])) {
                foreach ($metadata['icons'] as $key => $meta) {
                    if (isset($meta['search']) && isset($meta['search']['terms'])) {
                        if (\in_array($name, $meta['search']['terms'])) {
                            $name = $key;
                            break;
                        }
                    }
                }

                if (!isset($metadata['icons'][$name]) && '-o' === \substr($name, -2)) {
                    $name = \substr($name, 0, -2);
                }
            }
        }

        if (isset($metadata['icons'][$name])) {
            $icon = $metadata['icons'][$name];

            if (!$fontFamily) {
                if ($style && isset(self::$aliases[$style])) {
                    $fontFamily = self::$aliases[$style];
                } else {
                    $fontFamily = self::$aliases[\strtolower($icon['styles'][0])];
                }
            }

            return self::prepareIcon($icon['unicode'], $fontFamily);
        }

        return null;
    }

    /**
     * @return string[]
     */
    public static function cssResources(): array
    {
        return [
            self::$cdn.self::$version.'/css/all.css',
            self::$cdn.self::$version.'/css/v4-shims.css',
        ];
    }

    public static function cssCode(): string
    {
        $replace = [
            'Font Awesome 5 Pro' => null,
            'Font Awesome 5 Free' => null,
            'Font Awesome 5 Brands' => null,
            '../webfonts/' => self::$cdn.self::$version.'/webfonts/',
        ];

        $css = '';
        foreach (self::getCss() as $style => $content) {
            if (!$content) {
                continue;
            }

            foreach ($replace as $key => $value) {
                if (!$value) {
                    $value = self::$aliases[$style];
                }
                $content = \str_replace($key, $value, $content);
            }
            $css .= $content.PHP_EOL;

            if ('solid' == $style) {
                $css .= \str_replace(self::$aliases[$style], 'FontAwesome', $content).PHP_EOL;
            }
        }

        return $css;
    }

    public static function jsCode(): string
    {
        $version = \json_encode(self::$version);
        $aliases = \json_encode(self::$aliases);
        $metadata = \json_encode(self::getMetadata());

        $js = <<<JS

Ext.define('FontAwesome', {
    singleton: true,

    VERSION: $version,
    ALIASES: $aliases,
    METADATA: $metadata,
    
    // private
    prepareIcon: function(unicode, fontFamily) {
        return 'x' + unicode + '@' + fontFamily;
    },

    /**
     * @param value
     * @param style
     */
    unicode: function(value, style) {
        return this.prepareIcon(value, style && this.ALIASES[style] || 'FontAwesome');
    },

    /**
     * @param name
     * @param style
     */
    resolve: function(name, style) {
        var me = this;
        
        if (name.split('@').length > 1) {
            return name;
        }
        
        var fontFamily = null;
        
        if (name.indexOf('fa-') !== -1) {
            name = name.substr(3);
        }
        
        var aliases = me.ALIASES;
        var metadata = me.METADATA;

        var icons = metadata['icons'] || {};
        var shims = metadata['shims'] || {};

        if (!style) {
            if (shims[name]) {
                var shim = shims[name];
                if (shim['name'] || null) {
                    name = shim['name'];
                }
                var prefix = shim['prefix'] || null;
                if (prefix && aliases[prefix]) {
                    fontFamily = aliases[prefix];
                }
            } else if (!icons[name]) {
                for (var key in icons) {
                    var meta = icons[key];
                    if (meta['search'] && meta['search']['terms']) {
                        if (-1 !== meta['search']['terms'].indexOf(name)) {
                            name = key;
                            break;
                        }
                    }
                }
                
                if (!icons[name] && '-o' === name.substr(-2)) {
                    name = name.slice(0, -2);
                }
            }
        }

        if (icons[name] || null) {
            var icon = icons[name];

            if (!fontFamily) {
                if (style && aliases[style]) {
                    fontFamily = aliases[style];
                } else {
                    fontFamily = aliases[icon['styles'][0].toLowerCase()];
                }
            }

            return me.prepareIcon(icon['unicode'], fontFamily);
        }

        return;
    }
});

Ext.onReady(function() {
    Ext.setGlyphFontFamily('FontAwesome');
});

JS;

        return $js;
    }
}

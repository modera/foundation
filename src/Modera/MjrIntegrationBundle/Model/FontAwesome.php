<?php

namespace Modera\MjrIntegrationBundle\Model;

use Symfony\Component\Yaml\Yaml;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class FontAwesome
{
    /**
     * @var string
     */
    public static $version = '5.8.2';

    /**
     * @var string
     */
    public static $cdn = 'https://use.fontawesome.com/releases/v';

    /**
     * @var string|null
     */
    public static $bucket = 'https://raw.githubusercontent.com/FortAwesome/Font-Awesome';

    /**
     * @var string
     */
    public static $iconsYml = '/metadata/icons.yml';

    /**
     * @var string
     */
    public static $shimsYml = '/metadata/shims.yml';

    /**
     * @var bool
     */
    public static $rejectUnauthorized = true;

    /**
     * @var string|null
     */
    public static $cacheDir = null;

    /**
     * @var array
     */
    private static $cache = array();

    /**
     * @var array
     */
    private static $aliases = array(
        'fas' => 'FontAwesomeSolid',
        'far' => 'FontAwesomeRegular',
        'fal' => 'FontAwesomeLight',
        'fab' => 'FontAwesomeBrands',

        'solid'   => 'FontAwesomeSolid',
        'regular' => 'FontAwesomeRegular',
        'light'   => 'FontAwesomeLight',
        'brands'  => 'FontAwesomeBrands',
    );

    /**
     * @return array
     */
    private static function getCache()
    {
        if (count(self::$cache)) {
            return self::$cache;
        }

        $cacheDir = self::$cacheDir ?: dirname(__DIR__) . '/Resources/cache/font-awesome';
        $cacheVersion = substr(md5(join(';', array(
            self::$cdn,
            self::$bucket,
            self::$iconsYml,
            self::$shimsYml
        ))), 0, 5);
        $cachePathname = $cacheDir . '/' . self::$version . '-' . $cacheVersion . '.php';

        // create cache
        if (!file_exists($cachePathname)) {
            if (!file_exists($cacheDir)) {
                mkdir($cacheDir, 0777, true);
            }

            $context = stream_context_create(array(
                'ssl' => array(
                    'verify_peer' => self::$rejectUnauthorized,
                    'verify_peer_name' => self::$rejectUnauthorized,
                ),
            ));

            $iconsYml = self::$bucket . '/' . self::$version . self::$iconsYml;
            $shimsYml = self::$bucket . '/' . self::$version . self::$shimsYml;

            $metadata = array(
                'icons' => Yaml::parse(file_get_contents(self::$bucket ? $iconsYml : self::$iconsYml, false, $context)),
                'shims' => Yaml::parse(file_get_contents(self::$bucket ? $shimsYml : self::$shimsYml, false, $context)),
            );

            $css = array(
                'brands' => file_get_contents(self::$cdn . self::$version . '/css/brands.css', false, $context),
                'light' => @file_get_contents(self::$cdn . self::$version . '/css/light.css', false, $context),
                'regular' => file_get_contents(self::$cdn . self::$version . '/css/regular.css', false, $context),
                'solid' => file_get_contents(self::$cdn . self::$version . '/css/solid.css', false, $context),
            );

            file_put_contents($cachePathname, join(PHP_EOL, array(
                '<?php',
                'return ' . var_export(array(
                    'metadata' => $metadata,
                    'css' => $css,
                ), true) . ';',
                ''
            )));
        }

        self::$cache = require $cachePathname;

        return self::$cache;
    }

    /**
     * @return array
     */
    private static function getMetadata()
    {
        $cache = self::getCache();

        return $cache['metadata'];
    }

    /**
     * @return array
     */
    private static function getCss()
    {
        $cache = self::getCache();

        return $cache['css'];
    }

    /**
     * @param string $unicode
     * @param string $fontFamily
     *
     * @return string
     */
    private static function prepareIcon($unicode, $fontFamily)
    {
        return 'x' . $unicode . '@' . $fontFamily;
    }

    /**
     * @param string $value
     * @param string|null $style
     * @return string
     */
    public static function unicode($value, $style = null) {
        return self::prepareIcon($value, $style ? self::$aliases[$style] : 'FontAwesome');
    }

    /**
     * http://fontawesome.io/icons/.
     *
     * @param string $name
     * @param string|null $style
     *
     * @return string|null
     */
    public static function resolve($name, $style = null)
    {
        if (count(explode('@', $name)) > 1) {
            return $name;
        }

        $fontFamily = null;
        $metadata = self::getMetadata();

        if (false !== strrpos($name, 'fa-')) {
            $name = substr($name, 3);
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
            } else if (!isset($metadata['icons'][$name])) {
                foreach ($metadata['icons'] as $key => $meta) {
                    if (isset($meta['search']) && isset($meta['search']['terms'])) {
                        if (in_array($name, $meta['search']['terms'])) {
                            $name = $key;
                            break;
                        }
                    }
                }

                if (!isset($metadata['icons'][$name]) && '-o' === substr($name, -2)) {
                    $name = substr($name, 0, -2);
                }
            }
        }

        if (isset($metadata['icons'][$name])) {
            $icon = $metadata['icons'][$name];

            if (!$fontFamily) {
                if ($style && isset(self::$aliases[$style])) {
                    $fontFamily = self::$aliases[$style];
                } else {
                    $fontFamily = self::$aliases[strtolower($icon['styles'][0])];
                }
            }

            return self::prepareIcon($icon['unicode'], $fontFamily);
        }

        return;
    }

    /**
     * @return array
     */
    public static function cssResources()
    {
        return array(
            self::$cdn . self::$version . '/css/all.css',
            self::$cdn . self::$version . '/css/v4-shims.css',
        );
    }

    /**
     * @return string
     */
    public static function cssCode()
    {
        $replace = array(
            'Font Awesome 5 Pro'    => null,
            'Font Awesome 5 Free'   => null,
            'Font Awesome 5 Brands' => null,
            '../webfonts/' => self::$cdn . self::$version . '/webfonts/',
        );

        $css = '';
        foreach (self::getCss() as $style => $content) {
            if (!$content) {
                continue;
            }

            foreach ($replace as $key => $value) {
                if (!$value) {
                    $value = self::$aliases[$style];
                }
                $content = str_replace($key, $value, $content);
            }
            $css .= $content . PHP_EOL;

            if ('solid' == $style) {
                $css .= str_replace(self::$aliases[$style], 'FontAwesome', $content) . PHP_EOL;
            }
        }

        return $css;
    }

    /**
     * @return string
     */
    public static function jsCode()
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

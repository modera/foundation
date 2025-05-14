<?php

namespace Modera\MjrIntegrationBundle\Tests\Unit\Config;

use Modera\MjrIntegrationBundle\Model\FontAwesome;
use Symfony\Component\Yaml\Yaml;

class FontAwesomeTest extends \PHPUnit\Framework\TestCase
{
    private function prepareIcon(string $unicode, string $fontFamily): string
    {
        return 'x'.$unicode.'@'.$fontFamily;
    }

    public function testHowWellItWorks(): void
    {
        $shimsYml = FontAwesome::$bucket.'/'.FontAwesome::$version.FontAwesome::$shimsYml;
        $shims = Yaml::parse(\file_get_contents($shimsYml));

        $iconsYml = FontAwesome::$bucket.'/'.FontAwesome::$version.FontAwesome::$iconsYml;
        $icons = Yaml::parse(\file_get_contents($iconsYml));

        $mockIcons = [
            'address-book-o' => [
                'styles' => [null],
                'unicode' => null,
            ],
        ];

        $aliases = [
            'fas' => 'solid',
            'far' => 'regular',
            'fal' => 'light',
            'fab' => 'brands',
        ];

        // test Unicode
        foreach ($icons as $name => $icon) {
            $value = $this->prepareIcon($icon['unicode'], 'FontAwesome');
            $this->assertEquals(FontAwesome::unicode($icon['unicode']), $value);

            foreach ($icon['styles'] as $style) {
                $value = $this->prepareIcon($icon['unicode'], 'FontAwesome'.\ucfirst($style));
                $this->assertEquals(FontAwesome::unicode($icon['unicode'], $style), $value);
            }
        }

        // test resolve
        foreach (\array_merge($icons, $mockIcons) as $name => $icon) {
            foreach ($icon['styles'] as $style) {
                if (!$style) {
                    continue; // ignore mock icons
                }

                $value = $this->prepareIcon($icon['unicode'], 'FontAwesome'.\ucfirst($style));

                $this->assertEquals(FontAwesome::resolve($name, $style), $value);
                $this->assertEquals(FontAwesome::resolve('fa-'.$name, $style), $value);
                $this->assertEquals(FontAwesome::resolve($value, $style), $value);
            }

            // v4-shims
            $unicode = $icon['unicode'];
            $fontFamily = 'FontAwesome'.\ucfirst($icon['styles'][0] ?? '');

            if (isset($shims[$name])) {
                $shim = $shims[$name];

                if (isset($shim['name'])) {
                    $unicode = $icons[$shim['name']]['unicode'];
                }

                if (isset($shim['prefix']) && isset($aliases[$shim['prefix']])) {
                    $fontFamily = 'FontAwesome'.\ucfirst($aliases[$shim['prefix']]);
                }
            } elseif (!isset($icons[$name])) {
                foreach ($icons as $key => $meta) {
                    if (isset($meta['search']) && isset($meta['search']['terms'])) {
                        if (\in_array($name, $meta['search']['terms'])) {
                            $unicode = $icons[$key]['unicode'];
                            break;
                        }
                    }
                }

                if (!isset($icons[$name]) && '-o' === \substr($name, -2)) {
                    $key = \substr($name, 0, -2);
                    if (isset($icons[$key])) {
                        $unicode = $icons[$key]['unicode'];
                        $fontFamily = 'FontAwesome'.\ucfirst($icons[$key]['styles'][0]);
                    }
                }
            }

            $value = $this->prepareIcon($unicode, $fontFamily);

            $this->assertEquals(FontAwesome::resolve($name), $value);
            $this->assertEquals(FontAwesome::resolve('fa-'.$name), $value);
            $this->assertEquals(FontAwesome::resolve($value), $value);
        }

        $this->assertEquals(FontAwesome::resolve('not-found'), null);
        $this->assertEquals(FontAwesome::resolve('fa-not-found'), null);
        $this->assertEquals(FontAwesome::resolve('NOT_FOUND'), null);
    }
}

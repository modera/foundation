<?php

namespace Modera\MjrIntegrationBundle\Contributions;

use Modera\ExpanderBundle\Ext\AsContributorFor;
use Modera\ExpanderBundle\Ext\ContributorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Provides JavaScript files required for MJR to work.
 *
 * @copyright 2013 Modera Foundation
 */
#[AsContributorFor('modera_mjr_integration.js_resources')]
class JsResourcesProvider implements ContributorInterface
{
    /**
     * @param array{
     *     'extjs_path': string,
     *     'extjs_include_rtl': bool,
     *     'extjs_console_warnings': bool,
     *     'moment_js_version': string,
     * } $bundleConfig
     */
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly array $bundleConfig,
        private readonly string $kernelEnvironment,
    ) {
    }

    public function getItems(): array
    {
        // https://www.sencha.com/forum/showthread.php?142565
        // ext-all: minified, no JSDoc, no console warnings
        // ext-all-debug: non-minified, with JSDoc, no console warnings
        // ext-all-dev: non-minified, with JSDoc, with console warnings
        $extjs = $this->bundleConfig['extjs_path'].'/ext-all';
        if ($this->bundleConfig['extjs_include_rtl']) {
            $extjs .= '-rtl';
        }
        if ('dev' === $this->kernelEnvironment) {
            $extjs .= $this->bundleConfig['extjs_console_warnings'] ? '-dev' : '-debug-w-comments';
        }
        $extjs .= '.js';

        return [
            [
                'order' => PHP_INT_MIN + 5,
                'resource' => '//cdn.jsdelivr.net/npm/promise-polyfill@7/dist/polyfill.min.js',
            ],
            [
                'order' => PHP_INT_MIN + 5,
                'resource' => '//cdnjs.cloudflare.com/ajax/libs/moment.js/'.$this->bundleConfig['moment_js_version'].'/moment-with-locales.min.js',
            ],
            [
                'order' => PHP_INT_MIN + 5,
                'resource' => $extjs,
            ],
            [
                'order' => PHP_INT_MIN + 5,
                'resource' => $this->urlGenerator->generate('modera_font_awesome_js'),
            ],
            [
                'order' => PHP_INT_MIN + 5,
                'resource' => '/bundles/moderamjrintegration/js/orientationchange.js',
            ],
            [
                'order' => PHP_INT_MIN + 5,
                'resource' => '/bundles/moderamjrintegration/js/stylesheetsloader.js',
            ],
            [
                'order' => PHP_INT_MIN + 5,
                'resource' => '/bundles/moderamjrintegration/js/promisify.js',
            ],
            [
                'order' => PHP_INT_MIN + 5,
                'resource' => '/bundles/moderamjrintegration/js/rpc.js',
            ],
        ];
    }
}

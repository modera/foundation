<?php

namespace Modera\MjrIntegrationBundle\Contributions;

use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\MjrIntegrationBundle\DependencyInjection\ModeraMjrIntegrationExtension;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Provides JavaScript files required for MJR to work.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2013 Modera Foundation
 */
class JsResourcesProvider implements ContributorInterface
{
    private Router $router;

    /**
     * @var array{
     *     'extjs_path': string,
     *     'extjs_include_rtl': bool,
     *     'extjs_console_warnings': bool,
     *     'moment_js_version': string,
     * }
     */
    private array $bundleConfig;

    private bool $isDevEnv;

    public function __construct(ContainerInterface $container)
    {
        /** @var Router $router */
        $router = $container->get('router');
        $this->router = $router;

        /** @var array{
         *     'extjs_path': string,
         *     'extjs_include_rtl': bool,
         *     'extjs_console_warnings': bool,
         *     'moment_js_version': string,
         * } $bundleConfig */
        $bundleConfig = $container->getParameter(ModeraMjrIntegrationExtension::CONFIG_KEY);
        $this->bundleConfig = $bundleConfig;

        /** @var Kernel $kernel */
        $kernel = $container->get('kernel');
        $this->isDevEnv = 'dev' === $kernel->getEnvironment();
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
        if ($this->isDevEnv) {
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
                'resource' => $this->router->generate('modera_font_awesome_js'),
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
        ];
    }
}

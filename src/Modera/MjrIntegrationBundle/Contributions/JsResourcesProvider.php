<?php

namespace Modera\MjrIntegrationBundle\Contributions;

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Modera\MjrIntegrationBundle\DependencyInjection\ModeraMjrIntegrationExtension;
use Sli\ExpanderBundle\Ext\ContributorInterface;

/**
 * Provides JavaScript files required for MJR to work.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2013 Modera Foundation
 */
class JsResourcesProvider implements ContributorInterface
{
    /**
     * @var Router
     */
    private $router;

    /**
     * @var array
     */
    private $bundleConfig;

    /**
     * @var bool
     */
    private $isDevEnv;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->router = $container->get('router');
        $this->bundleConfig = $container->getParameter(ModeraMjrIntegrationExtension::CONFIG_KEY);

        /* @var Kernel $kernel */
        $kernel = $container->get('kernel');
        $this->isDevEnv = $kernel->getEnvironment() == 'dev';
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
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

        return array(
            array(
                'order' => PHP_INT_MIN + 5,
                'resource' => '//cdn.jsdelivr.net/npm/promise-polyfill@7/dist/polyfill.min.js',
            ),
            array(
                'order' => PHP_INT_MIN + 5,
                'resource' => '//cdnjs.cloudflare.com/ajax/libs/moment.js/' . $this->bundleConfig['moment_js_version'] . '/moment-with-locales.min.js',
            ),
            array(
                'order' => PHP_INT_MIN + 5,
                'resource' => $extjs,
            ),
            array(
                'order' => PHP_INT_MIN + 5,
                'resource' => $this->router->generate('modera_font_awesome_js'),
            ),
            array(
                'order' => PHP_INT_MIN + 5,
                'resource' => '/bundles/moderamjrintegration/js/orientationchange.js',
            ),
            array(
                'order' => PHP_INT_MIN + 5,
                'resource' => '/bundles/moderamjrintegration/js/stylesheetsloader.js',
            ),
            array(
                'order' => PHP_INT_MIN + 5,
                'resource' => '/bundles/moderamjrintegration/js/promisify.js',
            ),
        );
    }
}

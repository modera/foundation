<?php

namespace Modera\MJRThemeIntegrationBundle\Contributions;

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Modera\MjrIntegrationBundle\DependencyInjection\ModeraMjrIntegrationExtension;
use Modera\MJRThemeIntegrationBundle\DependencyInjection\ModeraMJRThemeIntegrationExtension;
use Sli\ExpanderBundle\Ext\ContributorInterface;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class CssResourcesProvider implements ContributorInterface
{
    /**
     * @var bool
     */
    private $isDevEnv;

    /**
     * @var array
     */
    private $themeIntegrationConfig;

    /**
     * @var array
     */
    private $mjrInteggrationConfig;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        /* @var Kernel $kernel */
        $kernel = $container->get('kernel');
        $this->isDevEnv = $kernel->getEnvironment() == 'dev';

        $this->themeIntegrationConfig = $container->getParameter(ModeraMJRThemeIntegrationExtension::CONFIG_KEY);
        $this->mjrInteggrationConfig = $container->getParameter(ModeraMjrIntegrationExtension::CONFIG_KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        $suffix = '';
        if ($this->mjrInteggrationConfig['extjs_include_rtl']) {
            $suffix .= '-rtl';
        }
        if ($this->isDevEnv) {
            $suffix .= '-debug';
        }
        $suffix .= '.css';

        return array(
            $this->themeIntegrationConfig['theme_path'].'/build/resources/modera-theme-all' . $suffix,
            $this->mjrInteggrationConfig['runtime_path'].'/build/resources/MJR-all' . $suffix,
        );
    }
}

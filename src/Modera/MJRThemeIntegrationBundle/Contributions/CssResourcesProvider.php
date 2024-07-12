<?php

namespace Modera\MJRThemeIntegrationBundle\Contributions;

use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\MjrIntegrationBundle\DependencyInjection\ModeraMjrIntegrationExtension;
use Modera\MJRThemeIntegrationBundle\DependencyInjection\ModeraMJRThemeIntegrationExtension;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class CssResourcesProvider implements ContributorInterface
{
    private bool $isDevEnv;

    /**
     * @var array{'theme_path': string}
     */
    private array $themeIntegrationConfig;

    /**
     * @var array{'runtime_path': string, 'extjs_include_rtl': bool}
     */
    private array $mjrIntegrationConfig;

    public function __construct(ContainerInterface $container)
    {
        /** @var Kernel $kernel */
        $kernel = $container->get('kernel');
        $this->isDevEnv = 'dev' === $kernel->getEnvironment();

        /** @var array{'theme_path': string} $themeIntegrationConfig */
        $themeIntegrationConfig = $container->getParameter(ModeraMJRThemeIntegrationExtension::CONFIG_KEY);
        $this->themeIntegrationConfig = $themeIntegrationConfig;

        /** @var array{'runtime_path': string, 'extjs_include_rtl': bool} $mjrIntegrationConfig */
        $mjrIntegrationConfig = $container->getParameter(ModeraMjrIntegrationExtension::CONFIG_KEY);
        $this->mjrIntegrationConfig = $mjrIntegrationConfig;
    }

    public function getItems(): array
    {
        $suffix = '';
        if ($this->mjrIntegrationConfig['extjs_include_rtl']) {
            $suffix .= '-rtl';
        }
        if ($this->isDevEnv) {
            $suffix .= '-debug';
        }
        $suffix .= '.css';

        return [
            $this->themeIntegrationConfig['theme_path'].'/build/resources/modera-theme-all'.$suffix,
            $this->mjrIntegrationConfig['runtime_path'].'/build/resources/MJR-all'.$suffix,
        ];
    }
}

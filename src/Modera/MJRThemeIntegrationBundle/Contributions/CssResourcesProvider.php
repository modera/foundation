<?php

namespace Modera\MJRThemeIntegrationBundle\Contributions;

use Modera\ExpanderBundle\Ext\AsContributorFor;
use Modera\ExpanderBundle\Ext\ContributorInterface;

/**
 * @copyright 2014 Modera Foundation
 */
#[AsContributorFor('modera_mjr_integration.css_resources')]
class CssResourcesProvider implements ContributorInterface
{
    /**
     * @param array{'theme_path': string}                              $themeIntegrationConfig
     * @param array{'runtime_path': string, 'extjs_include_rtl': bool} $mjrIntegrationConfig
     */
    public function __construct(
        private readonly array $themeIntegrationConfig,
        private readonly array $mjrIntegrationConfig,
        private readonly string $kernelEnvironment,
    ) {
    }

    public function getItems(): array
    {
        $suffix = '';
        if ($this->mjrIntegrationConfig['extjs_include_rtl']) {
            $suffix .= '-rtl';
        }
        if ('dev' === $this->kernelEnvironment) {
            $suffix .= '-debug';
        }
        $suffix .= '.css';

        return [
            $this->themeIntegrationConfig['theme_path'].'/build/resources/modera-theme-all'.$suffix,
            $this->mjrIntegrationConfig['runtime_path'].'/build/resources/MJR-all'.$suffix,
        ];
    }
}

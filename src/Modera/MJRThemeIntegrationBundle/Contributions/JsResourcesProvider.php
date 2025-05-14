<?php

namespace Modera\MJRThemeIntegrationBundle\Contributions;

use Modera\ExpanderBundle\Ext\AsContributorFor;
use Modera\ExpanderBundle\Ext\ContributorInterface;

/**
 * @copyright 2013 Modera Foundation
 */
#[AsContributorFor('modera_mjr_integration.js_resources')]
class JsResourcesProvider implements ContributorInterface
{
    /**
     * @param array{'theme_path': string} $themeIntegrationConfig
     */
    public function __construct(
        private readonly array $themeIntegrationConfig,
    ) {
    }

    public function getItems(): array
    {
        return [
            [
                'order' => PHP_INT_MIN + 10,
                'resource' => $this->themeIntegrationConfig['theme_path'].'/build/modera-theme.js',
            ],
        ];
    }
}

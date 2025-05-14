<?php

namespace Modera\BackendToolsSettingsBundle\Contributions;

use Modera\ExpanderBundle\Ext\AsContributorFor;
use Modera\ExpanderBundle\Ext\ContributorInterface;

/**
 * @copyright 2014 Modera Foundation
 */
#[AsContributorFor('modera_mjr_integration.config.config_mergers')]
class ConfigMergersProvider implements ContributorInterface
{
    public function __construct(
        private readonly SectionsConfigMerger $merger,
    ) {
    }

    public function getItems(): array
    {
        return [
            $this->merger,
        ];
    }
}

<?php

namespace Modera\DynamicallyConfigurableMJRBundle\Contributions;

use Modera\ConfigBundle\Manager\ConfigurationEntriesManagerInterface;
use Modera\DynamicallyConfigurableMJRBundle\ModeraDynamicallyConfigurableMJRBundle as Bundle;
use Modera\ExpanderBundle\Ext\AsContributorFor;
use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\MjrIntegrationBundle\Config\ConfigMergerInterface;

/**
 * @copyright 2020 Modera Foundation
 */
#[AsContributorFor('modera_mjr_integration.config.config_mergers')]
class ConfigMergersProvider implements ContributorInterface, ConfigMergerInterface
{
    public function __construct(
        private readonly ConfigurationEntriesManagerInterface $mgr,
    ) {
    }

    public function merge(array $existingConfig): array
    {
        $logoUrl = $this->mgr->findOneByNameOrDie(Bundle::CONFIG_LOGO_URL)->getValue();

        return \array_merge($existingConfig, [
            'modera_dynamically_configurable_mjr' => [
                'logo_url' => $logoUrl,
            ],
        ]);
    }

    public function getItems(): array
    {
        return [$this];
    }
}

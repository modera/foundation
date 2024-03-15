<?php

namespace Modera\DynamicallyConfigurableMJRBundle\Contributions;

use Modera\ConfigBundle\Manager\ConfigurationEntriesManagerInterface;
use Modera\DynamicallyConfigurableMJRBundle\ModeraDynamicallyConfigurableMJRBundle as Bundle;
use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\MjrIntegrationBundle\Config\ConfigMergerInterface;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2020 Modera Foundation
 */
class ConfigMergersProvider implements ContributorInterface, ConfigMergerInterface
{
    private ConfigurationEntriesManagerInterface $mgr;

    public function __construct(ConfigurationEntriesManagerInterface $mgr)
    {
        $this->mgr = $mgr;
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

<?php

namespace Modera\BackendTranslationsToolBundle\Contributions;

use Modera\ExpanderBundle\Ext\AsContributorFor;
use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\MjrIntegrationBundle\Config\ConfigMergerInterface;

/**
 * @copyright 2014 Modera Foundation
 */
#[AsContributorFor('modera_mjr_integration.config.config_mergers')]
class ConfigMergersProvider implements ContributorInterface, ConfigMergerInterface
{
    public function __construct(
        private readonly FiltersProvider $filtersProvider,
    ) {
    }

    public function merge(array $existingConfig): array
    {
        $filters = [];
        foreach ($this->filtersProvider->getItems() as $key => $arr) {
            if (!\is_array($arr)) {
                continue;
            }

            $filters[$key] = [];

            foreach ($arr as $iteratedFilter) {
                if (!$iteratedFilter->isAllowed()) {
                    continue;
                }

                $filters[$key][] = [
                    'id' => $iteratedFilter->getId(),
                    'name' => $iteratedFilter->getName(),
                ];
            }
        }

        return \array_merge($existingConfig, [
            'modera_backend_translations_tool' => [
                'filters' => $filters,
            ],
        ]);
    }

    public function getItems(): array
    {
        return [$this];
    }
}

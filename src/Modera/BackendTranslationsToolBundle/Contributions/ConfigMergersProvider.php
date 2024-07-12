<?php

namespace Modera\BackendTranslationsToolBundle\Contributions;

use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\MjrIntegrationBundle\Config\ConfigMergerInterface;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class ConfigMergersProvider implements ContributorInterface, ConfigMergerInterface
{
    private FiltersProvider $filtersProvider;

    public function __construct(FiltersProvider $filtersProvider)
    {
        $this->filtersProvider = $filtersProvider;
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

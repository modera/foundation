<?php

namespace Modera\BackendTranslationsToolBundle\Filtering\Filter;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class ObsoleteTranslationTokensFilter extends AbstractTranslationTokensFilter
{
    public function getId(): string
    {
        return 'obsolete';
    }

    public function getName(): string
    {
        return 'Obsolete';
    }

    public function getCount(array $params): int
    {
        if (!isset($params['filter']) || !\is_array($params['filter'])) {
            $params['filter'] = [];
        }
        $params['filter'] = \array_merge($this->getFilter(), $params['filter']);

        return parent::getCount($params);
    }

    public function getResult(array $params): array
    {
        if (!isset($params['filter']) || !\is_array($params['filter'])) {
            $params['filter'] = [];
        }
        $params['filter'] = \array_merge($this->getFilter(), $params['filter']);

        return parent::getResult($params);
    }

    /**
     * @return array<mixed>
     */
    private function getFilter(): array
    {
        return [
            ['property' => 'isObsolete', 'value' => 'eq:true'],
        ];
    }
}

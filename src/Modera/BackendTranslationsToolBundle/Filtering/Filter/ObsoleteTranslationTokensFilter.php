<?php

namespace Modera\BackendTranslationsToolBundle\Filtering\Filter;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class ObsoleteTranslationTokensFilter extends AbstractTranslationTokensFilter
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'obsolete';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Obsolete';
    }

    /**
     * {@inheritdoc}
     */
    public function getCount(array $params)
    {
        if (!isset($params['filter'])) {
            $params['filter'] = array();
        }
        $params['filter'] = array_merge($this->getFilter(), $params['filter']);

        return parent::getCount($params);
    }

    /**
     * {@inheritdoc}
     */
    public function getResult(array $params)
    {
        if (!isset($params['filter'])) {
            $params['filter'] = array();
        }
        $params['filter'] = array_merge($this->getFilter(), $params['filter']);

        return parent::getResult($params);
    }

    /**
     * @return array
     */
    private function getFilter()
    {
        return array(
            ['property' => 'isObsolete', 'value' => 'eq:true'],
        );
    }
}

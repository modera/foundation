<?php

namespace Modera\BackendTranslationsToolBundle\Contributions;

use Sli\ExpanderBundle\Ext\ContributorInterface;
use Modera\BackendTranslationsToolBundle\Filtering\Filter;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class FiltersProvider implements ContributorInterface
{
    /**
     * @var array
     */
    private $items;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        if (!$this->items) {
            $this->items = array(
                'translation_token' => array(
                    new Filter\AllTranslationTokensFilter($this->container),
                    new Filter\NewTranslationTokensFilter($this->container),
                    new Filter\ObsoleteTranslationTokensFilter($this->container),
                ),
            );
        }

        return $this->items;
    }
}

<?php

namespace Modera\BackendTranslationsToolBundle\Contributions;

use Modera\BackendTranslationsToolBundle\Filtering\Filter;
use Modera\BackendTranslationsToolBundle\Filtering\FilterInterface;
use Modera\ExpanderBundle\Ext\ContributorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class FiltersProvider implements ContributorInterface
{
    /**
     * @var array<string, FilterInterface[]>
     */
    private ?array $items = null;

    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getItems(): array
    {
        if (!$this->items) {
            $this->items = [
                'translation_token' => [
                    new Filter\AllTranslationTokensFilter($this->container),
                    new Filter\NewTranslationTokensFilter($this->container),
                    new Filter\ObsoleteTranslationTokensFilter($this->container),
                ],
            ];
        }

        return $this->items;
    }
}

<?php

namespace Modera\BackendTranslationsToolBundle\Contributions;

use Modera\BackendTranslationsToolBundle\Filtering\Filter;
use Modera\BackendTranslationsToolBundle\Filtering\FilterInterface;
use Modera\ExpanderBundle\Ext\AsContributorFor;
use Modera\ExpanderBundle\Ext\ContributorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @copyright 2014 Modera Foundation
 */
#[AsContributorFor('modera_backend_translations_tool.filters')]
class FiltersProvider implements ContributorInterface
{
    /**
     * @var ?array<string, ?FilterInterface[]>
     */
    private ?array $items = null;

    public function __construct(
        private readonly ContainerInterface $container,
    ) {
    }

    /**
     * @return array<string, ?FilterInterface[]>
     */
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

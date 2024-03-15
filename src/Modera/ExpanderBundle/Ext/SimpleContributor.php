<?php

namespace Modera\ExpanderBundle\Ext;

class SimpleContributor implements ContributorInterface
{
    /**
     * @var object[]
     */
    private array $items = [];

    /**
     * @param object[] $items
     */
    public function __construct(array $items = [])
    {
        foreach ($items as $item) {
            $this->addItem($item);
        }
    }

    public function addItem(object $item): void
    {
        $this->items[\spl_object_hash($item)] = $item;
    }

    public function getItems(): array
    {
        return $this->items;
    }
}

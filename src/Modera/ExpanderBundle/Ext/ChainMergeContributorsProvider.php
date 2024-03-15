<?php

namespace Modera\ExpanderBundle\Ext;

/**
 * This provider know how to deal with aggregated providers that happen to implement OrderedContributorInterface
 * interface. If there are several providers that have the same order specified, then LIFO method is used to resolve
 * the best one. If some providers do not implement this interface, then they will be appended to the end of all
 * providers ( when #getItems() method is invoked ).
 */
class ChainMergeContributorsProvider implements ChainContributorsProviderInterface
{
    /**
     * @var ContributorInterface[]
     */
    private array $contributors = [];

    public function addContributor(ContributorInterface $contributor): void
    {
        $this->contributors[] = $contributor;
    }

    public function getContributors(): array
    {
        return $this->contributors;
    }

    public function getItems(): array
    {
        /** @var OrderedContributorInterface[] $orderedContributors */
        $orderedContributors = [];

        /** @var ContributorInterface[] $plainContributors */
        $plainContributors = [];

        foreach ($this->contributors as $contributor) {
            if ($contributor instanceof OrderedContributorInterface) {
                $orderedContributors[] = $contributor;
            } else {
                $plainContributors[] = $contributor;
            }
        }

        // @ is required to avoid having errors thrown by some versions of PHP
        @\usort($orderedContributors, static function (OrderedContributorInterface $a, OrderedContributorInterface $b): int {
            if ($a->getOrder() === $b->getOrder()) {
                return 0;
            }

            return $b->getOrder() < $a->getOrder() ? 1 : -1;
        });

        /** @var ContributorInterface[] $contributors */
        $contributors = \array_merge($orderedContributors, $plainContributors);

        $result = [];
        foreach ($contributors as $contributor) {
            $contributorResult = $contributor->getItems();
            $result = \array_merge($result, $contributorResult);
        }

        return $result;
    }
}

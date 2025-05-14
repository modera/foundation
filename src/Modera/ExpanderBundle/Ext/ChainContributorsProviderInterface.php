<?php

namespace Modera\ExpanderBundle\Ext;

/**
 * Implementations of this interface are to be capable of merging contributed items provided by other
 * contributors in some way.
 *
 * @copyright 2024 Modera Foundation
 */
interface ChainContributorsProviderInterface extends ContributorInterface
{
    public function addContributor(ContributorInterface $contributor): void;

    /**
     * @return ContributorInterface[]
     */
    public function getContributors(): array;
}

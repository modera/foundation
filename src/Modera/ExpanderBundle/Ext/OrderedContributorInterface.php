<?php

namespace Modera\ExpanderBundle\Ext;

/**
 * This interface is used by {@class ChainMergeContributorsProvider}.
 *
 * Your implementations of {@class ContributorInterface} may optionally implement this interface when you need to achieve
 * a certain sorting when contributions are being merged.
 */
interface OrderedContributorInterface extends ContributorInterface
{
    public function getOrder(): int;
}

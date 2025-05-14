<?php

namespace Modera\ExpanderBundle\Ext;

/**
 * This base interface describes a contract that your application logic may rely upon when it needs to consume
 * contributed to a certain extension points entries.
 *
 * @copyright 2024 Modera Foundation
 */
interface ContributorInterface
{
    /**
     * @return mixed[]
     */
    public function getItems(): array;
}

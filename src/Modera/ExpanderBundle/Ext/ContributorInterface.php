<?php

namespace Modera\ExpanderBundle\Ext;

/**
 * This base interface describes a contract that your application logic may rely upon when it needs to consume
 * contributed to a certain extension points entries.
 */
interface ContributorInterface
{
    public const CLAZZ = 'Modera\ExpanderBundle\Ext\ContributorInterface';

    /**
     * @return mixed[]
     */
    public function getItems(): array;
}

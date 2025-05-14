<?php

namespace Modera\ExpanderBundle\Ext;

/**
 * @copyright 2025 Modera Foundation
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class AsContributorFor
{
    public function __construct(
        public readonly string $id,
    ) {
    }
}

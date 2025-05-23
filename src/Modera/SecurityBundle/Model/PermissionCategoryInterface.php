<?php

namespace Modera\SecurityBundle\Model;

/**
 * Allows to categorize implementations of {@class PermissionInterface}s.
 *
 * @copyright 2014 Modera Foundation
 */
interface PermissionCategoryInterface
{
    /**
     * A unique ID that can later be used by {@class PermissionInterface} to refer a category, for example:
     * "customer_support".
     */
    public function getTechnicalName(): string;

    /**
     * A human-readable name of a category, for example - "Customer support".
     */
    public function getName(): string;
}

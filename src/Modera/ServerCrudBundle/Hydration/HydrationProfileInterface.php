<?php

namespace Modera\ServerCrudBundle\Hydration;

/**
 * @copyright 2013 Modera Foundation
 */
interface HydrationProfileInterface
{
    /**
     * @return string[]
     */
    public function getGroups(): array;
}

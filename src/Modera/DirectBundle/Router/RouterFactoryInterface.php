<?php

namespace Modera\DirectBundle\Router;

use Symfony\Component\HttpFoundation\Request;

/**
 * @copyright 2025 Modera Foundation
 */
interface RouterFactoryInterface
{
    public function create(Request $request): Router;
}

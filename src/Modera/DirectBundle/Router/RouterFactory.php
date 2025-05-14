<?php

namespace Modera\DirectBundle\Router;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @copyright 2025 Modera Foundation
 */
class RouterFactory implements RouterFactoryInterface
{
    public function __construct(
        private readonly ContainerInterface $container,
    ) {
    }

    public function create(Request $request): Router
    {
        return new Router($this->container);
    }
}

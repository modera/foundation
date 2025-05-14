<?php

namespace Modera\DirectBundle\Api;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @copyright 2025 Modera Foundation
 */
class ApiFactory
{
    public function __construct(
        private readonly ContainerInterface $container,
    ) {
    }

    public function create(): Api
    {
        return new Api($this->container);
    }
}

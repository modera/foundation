<?php

namespace Modera\ExpanderBundle\Tests\Unit\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;

class MockContainerBuilder extends ContainerBuilder
{
    public $services = [];

    public array $definitions = [];

    public function findTaggedServiceIds(string $name, bool $throwOnAbstract = false): array
    {
        return $this->services;
    }

    public function addDefinitions(array $definitions): void
    {
        $this->definitions = \array_merge($this->definitions, $definitions);
    }
}

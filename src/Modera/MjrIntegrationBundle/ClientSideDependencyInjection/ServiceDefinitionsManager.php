<?php

namespace Modera\MjrIntegrationBundle\ClientSideDependencyInjection;

use Modera\ExpanderBundle\Ext\ContributorInterface;

/**
 * Provides an access to service side dependency injection container service definitions.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2013 Modera Foundation
 */
class ServiceDefinitionsManager
{
    private ContributorInterface $provider;

    public function __construct(ContributorInterface $provider)
    {
        $this->provider = $provider;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getDefinitions(): array
    {
        /** @var array<string, array<string, mixed>> $items */
        $items = $this->provider->getItems();

        return $items;
    }

    /**
     * @return ?array<string, mixed>
     */
    public function getDefinition(string $id): ?array
    {
        $definitions = $this->getDefinitions();

        return $definitions[$id] ?? null;
    }
}

<?php

namespace Modera\MjrIntegrationBundle\ClientSideDependencyInjection;

use Modera\ExpanderBundle\Ext\ExtensionProvider;

/**
 * Provides access to service side dependency injection container service definitions.
 *
 * @copyright 2013 Modera Foundation
 */
class ServiceDefinitionsManager
{
    public function __construct(
        private readonly ExtensionProvider $extensionProvider,
    ) {
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getDefinitions(): array
    {
        /** @var array<string, array<string, mixed>> $items */
        $items = $this->extensionProvider->get('modera_mjr_integration.csdi.service_definitions')->getItems();

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

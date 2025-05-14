<?php

namespace Modera\ConfigBundle\Config;

/**
 * Use this class to define your configuration properties in config-entries-providers.
 *
 * @copyright 2014 Modera Foundation
 */
class ConfigurationEntryDefinition
{
    /**
     * @param array<mixed> $serverHandlerConfig
     * @param array<mixed> $clientHandlerConfig
     */
    public function __construct(
        private readonly string $name,
        private readonly string $readableName,
        private readonly mixed $value,
        private readonly string $category,
        private readonly array $serverHandlerConfig = [],
        private readonly array $clientHandlerConfig = [],
        private readonly bool $isReadOnly = false,
        private readonly bool $isExposed = true,
    ) {
    }

    public function isExposed(): bool
    {
        return $this->isExposed;
    }

    public function isReadOnly(): bool
    {
        return $this->isReadOnly;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getReadableName(): string
    {
        return $this->readableName;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * @return array<mixed>
     */
    public function getClientHandlerConfig(): array
    {
        return $this->clientHandlerConfig;
    }

    /**
     * @return array<mixed>
     */
    public function getServerHandlerConfig(): array
    {
        return $this->serverHandlerConfig;
    }

    public function getCategory(): string
    {
        return $this->category;
    }
}

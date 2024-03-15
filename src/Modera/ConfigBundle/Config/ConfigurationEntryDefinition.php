<?php

namespace Modera\ConfigBundle\Config;

/**
 * Use this class to define your configuration properties in config-entries-providers.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class ConfigurationEntryDefinition
{
    private string $name;

    private string $readableName;

    /**
     * @var mixed Mixed value
     */
    private $value;

    private string $category;

    /**
     * @var array<mixed>
     */
    private array $serverHandlerConfig;

    /**
     * @var array<mixed>
     */
    private array $clientHandlerConfig;

    private bool $isExposed;

    private bool $isReadOnly;

    /**
     * @param mixed        $value               Mixed value
     * @param array<mixed> $serverHandlerConfig
     * @param array<mixed> $clientHandlerConfig
     */
    public function __construct(
        string $name,
        string $readableName,
        $value,
        string $category,
        ?array $serverHandlerConfig = null,
        ?array $clientHandlerConfig = null,
        bool $isReadOnly = false,
        bool $isExposed = true
    ) {
        $this->name = $name;
        $this->readableName = $readableName;
        $this->value = $value;
        $this->category = $category;
        $this->serverHandlerConfig = $serverHandlerConfig ?: [];
        $this->clientHandlerConfig = $clientHandlerConfig ?: [];
        $this->isReadOnly = $isReadOnly;
        $this->isExposed = $isExposed;
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

    /**
     * @return mixed Mixed value
     */
    public function getValue()
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

    public function setCategory(string $category): void
    {
        $this->category = $category;
    }

    public function getCategory(): string
    {
        return $this->category;
    }
}

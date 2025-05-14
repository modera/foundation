<?php

namespace Modera\MjrIntegrationBundle\Sections;

/**
 * A default immutable implementation.
 *
 * @copyright 2013 Modera Foundation
 */
class Section implements SectionInterface
{
    /**
     * @param mixed[] $metadata
     */
    public function __construct(
        private readonly string $id,
        private readonly string $controller,
        private readonly array $metadata = [],
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getController(): string
    {
        return $this->controller;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }
}

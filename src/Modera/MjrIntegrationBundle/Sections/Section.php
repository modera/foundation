<?php

namespace Modera\MjrIntegrationBundle\Sections;

/**
 * A default immutable implementation.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2013 Modera Foundation
 */
class Section implements SectionInterface
{
    private string $id;

    private string $controller;

    /**
     * @var mixed[]
     */
    private array $metadata;

    /**
     * @param mixed[] $metadata
     */
    public function __construct(string $id, string $controller, array $metadata = [])
    {
        $this->id = $id;
        $this->controller = $controller;
        $this->metadata = $metadata;
    }

    public function getController(): string
    {
        return $this->controller;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }
}

<?php

namespace Modera\BackendToolsSettingsBundle\Section;

/**
 * @copyright 2014 Modera Foundation
 */
class StandardSection implements NestedSectionInterface
{
    /**
     * @param array<mixed> $meta
     */
    public function __construct(
        private readonly string $id,
        private readonly string $name,
        private readonly string $activityClass,
        private readonly ?string $glyph = null,
        private readonly array $meta = [],
        private readonly ?string $parent = null,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getActivityClass(): string
    {
        return $this->activityClass;
    }

    public function getGlyph(): ?string
    {
        return $this->glyph;
    }

    public function getMeta(): array
    {
        return $this->meta;
    }

    public function getParent(): ?string
    {
        return $this->parent;
    }
}

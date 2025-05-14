<?php

namespace Modera\BackendToolsBundle\Section;

/**
 * A basic immutable implementation of {@class SectionInterface}.
 *
 * @copyright 2013 Modera Foundation
 */
class Section implements SectionInterface
{
    /**
     * @param array<mixed> $sectionActivationParams
     * @param array<mixed> $meta
     */
    public function __construct(
        private readonly string $name,
        private readonly string $section,
        private readonly string $description = '',
        private readonly string $glyph = '',
        private readonly string $iconSrc = '',
        private readonly string $iconClass = '',
        private readonly array $sectionActivationParams = [],
        private readonly array $meta = [],
    ) {
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getGlyph(): string
    {
        return $this->glyph;
    }

    public function getIconSrc(): string
    {
        return $this->iconSrc;
    }

    public function getIconClass(): string
    {
        return $this->iconClass;
    }

    public function getMeta(): array
    {
        return $this->meta;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSection(): string
    {
        return $this->section;
    }

    public function getSectionActivationParams(): array
    {
        return $this->sectionActivationParams;
    }
}

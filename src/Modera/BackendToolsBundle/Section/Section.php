<?php

namespace Modera\BackendToolsBundle\Section;

/**
 * A basic immutable implementation of {@class SectionInterface}.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2013 Modera Foundation
 */
class Section implements SectionInterface
{
    private string $glyph;
    private string $iconSrc;
    private string $iconClass;
    private string $name;
    private string $description;
    /**
     * @var array<mixed>
     */
    private array $meta;
    private string $section;
    /**
     * @var array<mixed>
     */
    private array $sectionActivationParams;

    /**
     * @param array<mixed> $sectionActivationParams
     * @param array<mixed> $meta
     */
    public function __construct(
        string $name,
        string $section,
        string $description = '',
        string $glyph = '',
        string $iconSrc = '',
        string $iconClass = '',
        array $sectionActivationParams = [],
        array $meta = []
    ) {
        $this->name = $name;
        $this->section = $section;
        $this->description = $description;
        $this->glyph = $glyph;
        $this->iconSrc = $iconSrc;
        $this->iconClass = $iconClass;
        $this->sectionActivationParams = $sectionActivationParams;
        $this->meta = $meta;
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

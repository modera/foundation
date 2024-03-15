<?php

namespace Modera\BackendToolsSettingsBundle\Section;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class StandardSection implements SectionInterface
{
    private string $id;
    private string $name;
    private string $activityClass;
    private ?string $glyph;
    /**
     * @var array<mixed>
     */
    private array $meta;

    /**
     * @param array<mixed> $meta
     */
    public function __construct(
        string $id,
        string $name,
        string $activityClass,
        ?string $glyph = null,
        array $meta = []
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->activityClass = $activityClass;
        $this->glyph = $glyph;
        $this->meta = $meta;
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
}

<?php

namespace Modera\BackendSecurityBundle\Section;

/**
 * @copyright 2021 Modera Foundation
 */
class Section implements SectionInterface
{
    public function __construct(
        private readonly string $id,
        private readonly string $title,
        private readonly string $glyphIcon,
        private readonly string $uiClass,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getGlyphIcon(): string
    {
        return $this->glyphIcon;
    }

    public function getUiClass(): string
    {
        return $this->uiClass;
    }
}

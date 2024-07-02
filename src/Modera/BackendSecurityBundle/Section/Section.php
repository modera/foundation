<?php

namespace Modera\BackendSecurityBundle\Section;

/**
 * @author    Artem Brovko <artem.brovko@modera.com>
 * @copyright 2021 Modera Foundation
 */
class Section implements SectionInterface
{
    private string $id;

    private string $title;

    private string $glyphIcon;

    private string $uiClass;

    public function __construct(string $id, string $title, string $glyphIcon, string $uiClass)
    {
        $this->id = $id;
        $this->title = $title;
        $this->glyphIcon = $glyphIcon;
        $this->uiClass = $uiClass;
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

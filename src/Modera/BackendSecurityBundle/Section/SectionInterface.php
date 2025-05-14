<?php

namespace Modera\BackendSecurityBundle\Section;

/**
 * @copyright 2021 Modera Foundation
 */
interface SectionInterface
{
    public function getId(): string;

    public function getTitle(): string;

    public function getGlyphIcon(): string;

    public function getUiClass(): string;
}

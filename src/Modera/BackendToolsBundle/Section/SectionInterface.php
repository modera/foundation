<?php

namespace Modera\BackendToolsBundle\Section;

/**
 * Represents an item that will be displayed in backend' Tools section.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2013 Modera Foundation
 */
interface SectionInterface
{
    public function getGlyph(): string;

    public function getIconSrc(): string;

    public function getIconClass(): string;

    public function getName(): string;

    public function getDescription(): string;

    /**
     * Optional metadata.
     *
     * @return array<mixed>
     */
    public function getMeta(): array;

    /**
     * ID of a section to activate.
     */
    public function getSection(): string;

    /**
     * @return array<mixed>
     */
    public function getSectionActivationParams(): array;
}

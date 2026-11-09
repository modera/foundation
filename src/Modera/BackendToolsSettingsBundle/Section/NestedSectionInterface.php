<?php

declare(strict_types=1);

namespace Modera\BackendToolsSettingsBundle\Section;

/**
 * A settings section that is shown as a submenu item of another section.
 *
 * @copyright 2026 Modera Foundation
 */
interface NestedSectionInterface extends SectionInterface
{
    /**
     * ID of the section this one is nested under, null for a top-level section.
     */
    public function getParent(): ?string;
}

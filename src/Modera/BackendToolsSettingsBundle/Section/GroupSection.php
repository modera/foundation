<?php

declare(strict_types=1);

namespace Modera\BackendToolsSettingsBundle\Section;

/**
 * A menu item that only groups subsections: clicking it expands or collapses them, it opens nothing itself.
 *
 * @copyright 2026 Modera Foundation
 */
class GroupSection extends StandardSection
{
    /**
     * @param array<mixed> $meta
     */
    public function __construct(
        string $id,
        string $name,
        ?string $glyph = null,
        array $meta = [],
        ?string $parent = null,
    ) {
        parent::__construct($id, $name, '', $glyph, $meta, $parent);
    }
}

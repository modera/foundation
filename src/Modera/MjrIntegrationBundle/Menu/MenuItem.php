<?php

namespace Modera\MjrIntegrationBundle\Menu;

use Modera\MjrIntegrationBundle\Sections\Section;

/**
 * Default immutable implementation.
 *
 * @copyright 2013 Modera Foundation
 */
class MenuItem extends Section
{
    /**
     * @param mixed[] $metadata
     */
    public function __construct(
        private readonly string $label,
        string $controller,
        string $id,
        array $metadata = [],
        private readonly ?string $glyph = null,
    ) {
        parent::__construct($id, $controller, $metadata);
    }

    public function getGlyph(): ?string
    {
        return $this->glyph;
    }

    public function getLabel(): string
    {
        return $this->label;
    }
}

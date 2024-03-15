<?php

namespace Modera\MjrIntegrationBundle\Menu;

use Modera\MjrIntegrationBundle\Sections\Section;

/**
 * Default immutable implementation.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2013 Modera Foundation
 */
class MenuItem extends Section
{
    private ?string $glyph;

    private string $label;

    /**
     * @param mixed[] $metadata
     */
    public function __construct(string $label, string $controller, string $id, array $metadata = [], ?string $glyph = null)
    {
        $this->glyph = $glyph;
        $this->label = $label;

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

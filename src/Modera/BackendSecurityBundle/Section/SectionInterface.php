<?php

namespace Modera\BackendSecurityBundle\Section;

/**
 * @author    Artem Brovko <artem.brovko@modera.com>
 * @copyright 2021 Modera Foundation
 */
interface SectionInterface
{
    /**
     * @return string
     */
    public function getId();

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @return string
     */
    public function getGlyphIcon();

    /**
     * @return string
     */
    public function getUiClass();
}

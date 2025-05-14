<?php

namespace Modera\BackendLanguagesBundle\Service;

/**
 * @copyright 2020 Modera Foundation
 */
interface SanitizeInterface
{
    public function sanitizeHtml(string $content): string;
}

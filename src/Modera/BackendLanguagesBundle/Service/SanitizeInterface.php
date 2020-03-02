<?php

namespace Modera\BackendLanguagesBundle\Service;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2020 Modera Foundation
 */
interface SanitizeInterface
{
    /**
     * @param string $content
     * @return string
     */
    public function sanitizeHtml($content);
}

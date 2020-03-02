<?php

namespace Modera\BackendLanguagesBundle\Service;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2020 Modera Foundation
 */
class SanitizationService implements SanitizeInterface
{
    /**
     * {@inheritdoc}
     */
    public function sanitizeHtml($content)
    {
        return $content;
    }
}

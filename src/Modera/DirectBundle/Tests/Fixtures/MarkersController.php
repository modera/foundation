<?php

namespace Modera\DirectBundle\Tests\Fixtures;

use Modera\DirectBundle\Attribute\Anonymous;
use Modera\DirectBundle\Attribute\Form;
use Modera\DirectBundle\Attribute\Remote;
use Modera\DirectBundle\Attribute\Secure;

/**
 * @copyright 2026 Modera Foundation
 */
class MarkersController
{
    #[Remote]
    public function attributeAction(array $params): array
    {
        return $params;
    }

    #[Remote]
    #[Form]
    public function attributeFormAction(array $params, array $files): bool
    {
        return true;
    }

    /**
     * @Remote
     */
    public function docblockAction(array $params): array
    {
        return $params;
    }

    /**
     * @Remote
     *
     * @Form
     */
    public function docblockFormAction(array $params, array $files): bool
    {
        return true;
    }

    public function hiddenAction(array $params): array
    {
        return $params;
    }

    #[Remote]
    #[Secure]
    public function attributeSecureAction(array $params): array
    {
        return $params;
    }

    #[Remote]
    #[Anonymous]
    public function attributeAnonymousAction(array $params): array
    {
        return $params;
    }

    /**
     * @Remote
     *
     * @Secure
     */
    public function docblockSecureAction(array $params): array
    {
        return $params;
    }

    /**
     * @Remote
     *
     * @Anonymous
     */
    public function docblockAnonymousAction(array $params): array
    {
        return $params;
    }
}

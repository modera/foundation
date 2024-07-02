<?php

namespace Modera\ExpanderBundle\Tests\Unit;

use Modera\ExpanderBundle\Contributing\ExtensionPointsAwareBundleInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class FooDummyBundle extends Bundle implements ExtensionPointsAwareBundleInterface
{
    public ?array $map = null;

    public function getExtensionPointContributions(): array
    {
        return $this->map ?? [];
    }
}

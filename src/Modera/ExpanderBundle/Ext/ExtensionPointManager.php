<?php

namespace Modera\ExpanderBundle\Ext;

/**
 * @internal
 *
 * @copyright 2025 Modera Foundation
 */
class ExtensionPointManager
{
    /**
     * @param array<string, string> $extensionPoints
     */
    public function __construct(
        private readonly array $extensionPoints = [],
    ) {
    }

    public function has(string $id): bool
    {
        return isset($this->extensionPoints[$id]);
    }

    public function get(string $id): ?ExtensionPoint
    {
        if (isset($this->extensionPoints[$id])) {
            /** @var ExtensionPoint $extensionPoint */
            $extensionPoint = \unserialize($this->extensionPoints[$id]);

            return $extensionPoint;
        }

        return null;
    }
}

<?php

namespace Modera\ExpanderBundle\Command;

use Modera\ExpanderBundle\Ext\ExtensionPoint;
use Symfony\Component\Console\Command\Command;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @copyright 2024 Modera Foundation
 */
abstract class AbstractCommand extends Command
{
    /**
     * @var array<string, string>
     */
    private array $extensionPoints = [];

    /**
     * @param array<string, string> $extensionPoints
     */
    #[Required]
    public function setExtensionPoints(array $extensionPoints): void
    {
        $this->extensionPoints = $extensionPoints;
    }

    /**
     * @return array<string, ExtensionPoint>
     */
    protected function getExtensionPoints(): array
    {
        $extensionPoints = [];
        foreach ($this->extensionPoints as $id => $data) {
            /** @var ExtensionPoint $extensionPoint */
            $extensionPoint = \unserialize($data);
            $extensionPoints[$id] = $extensionPoint;
        }

        return $extensionPoints;
    }

    protected function getExtensionPoint(string $id): ?ExtensionPoint
    {
        if (isset($this->extensionPoints[$id])) {
            /** @var ExtensionPoint $extensionPoint */
            $extensionPoint = \unserialize($this->extensionPoints[$id]);

            return $extensionPoint;
        }

        return null;
    }
}

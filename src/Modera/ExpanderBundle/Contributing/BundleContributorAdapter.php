<?php

namespace Modera\ExpanderBundle\Contributing;

use Modera\ExpanderBundle\Ext\ContributorInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @internal
 *
 * This class is not part of a public API.
 *
 * This class is used internally by {@class ExtensionPointsAwareBundlesCollectorCompilerPass}.
 *
 * @copyright 2024 Modera Foundation
 */
class BundleContributorAdapter implements ContributorInterface
{
    public function __construct(
        private readonly KernelInterface $kernel,
        private readonly string $bundleName,
        private readonly string $extensionPointId,
    ) {
    }

    public function getItems(): array
    {
        $bundle = $this->kernel->getBundle($this->bundleName);
        if ($bundle instanceof ExtensionPointsAwareBundleInterface) {
            $contributions = $bundle->getExtensionPointContributions();

            if (\is_callable($contributions[$this->extensionPointId] ?? null)) {
                /** @var mixed[] $contributions */
                $contributions = $contributions[$this->extensionPointId]($this->kernel->getContainer());

                return $contributions;
            }

            if (\is_array($contributions[$this->extensionPointId] ?? null)) {
                return $contributions[$this->extensionPointId];
            }
        } else {
            throw new \InvalidArgumentException(\sprintf("Bundle '%s' doesn't implement ExtensionPointsAwareBundleInterface interface", \get_class($bundle)));
        }

        return [];
    }
}

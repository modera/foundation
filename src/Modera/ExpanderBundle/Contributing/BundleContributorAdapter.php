<?php

namespace Modera\ExpanderBundle\Contributing;

use Modera\ExpanderBundle\Ext\ContributorInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * This class is not part of a public API.
 *
 * This class is used internally by {@class ExtensionPointsAwareBundlesCollectorCompilerPass}.
 */
class BundleContributorAdapter implements ContributorInterface
{
    private KernelInterface $kernel;

    private string $bundleName;

    private string $extensionPointName;

    public function __construct(KernelInterface $kernel, string $bundleName, string $extensionPointName)
    {
        $this->kernel = $kernel;
        $this->bundleName = $bundleName;
        $this->extensionPointName = $extensionPointName;
    }

    public function getItems(): array
    {
        $bundle = $this->kernel->getBundle($this->bundleName);
        if ($bundle instanceof ExtensionPointsAwareBundleInterface) {
            $contributions = $bundle->getExtensionPointContributions();

            if (\is_array($contributions) && is_array($contributions[$this->extensionPointName] ?? null)) {
                return $contributions[$this->extensionPointName];
            }
        } else {
            throw new \InvalidArgumentException(\sprintf("Bundle '%s' doesn't implement ExtensionPointsAwareBundleInterface interface", \get_class($bundle)));
        }

        return [];
    }
}

<?php

namespace Modera\ExpanderBundle\Ext;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @copyright 2025 Modera Foundation
 */
class ExtensionProvider
{
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly ExtensionPointManager $extensionPointManager,
    ) {
    }

    private function getExtensionPoint(string $id): ?ExtensionPoint
    {
        return $this->extensionPointManager->get($id);
    }

    public function has(string $id): bool
    {
        $extensionPoint = $this->getExtensionPoint($id);

        return $extensionPoint && $this->container->has($extensionPoint->getServiceId());
    }

    public function get(string $id): ContributorInterface
    {
        $extensionPoint = $this->getExtensionPoint($id);
        if (!$extensionPoint) {
            throw new \RuntimeException(\sprintf('Unable to find an extension point with ID "%s".', $id));
        }

        /** @var ContributorInterface $provider */
        $provider = $this->container->get($extensionPoint->getServiceId());

        return $provider;
    }
}

<?php

namespace Modera\ExpanderBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @internal
 *
 * @copyright 2025 Modera Foundation
 */
class DumpExtensionPointsCompilerPass implements CompilerPassInterface
{
    use ExtensionPointsCompilerPassTrait;

    public function process(ContainerBuilder $container): void
    {
        $extensionPoints = [];
        foreach ($this->getExtensionPoints($container) as $extensionPoint) {
            $extensionPoints[$extensionPoint->getId()] = \serialize($extensionPoint);
        }

        $container->setParameter('modera_expander.extension_points', $extensionPoints);
    }
}

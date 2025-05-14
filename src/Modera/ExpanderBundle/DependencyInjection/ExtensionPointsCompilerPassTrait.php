<?php

namespace Modera\ExpanderBundle\DependencyInjection;

use Modera\ExpanderBundle\Ext\ExtensionPoint;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @internal
 *
 * @copyright 2025 Modera Foundation
 */
trait ExtensionPointsCompilerPassTrait
{
    /**
     * @return ExtensionPoint[]
     */
    private function getExtensionPoints(ContainerBuilder $container): array
    {
        /** @var ExtensionPoint[] $result */
        $result = [];

        foreach ($container->getCompiler()->getPassConfig()->getPasses() as $pass) {
            if ($pass instanceof ExtensionPointAwareCompilerPassInterface && $pass->getExtensionPoint()) {
                $result[] = $pass->getExtensionPoint();
            }
        }

        return $result;
    }
}

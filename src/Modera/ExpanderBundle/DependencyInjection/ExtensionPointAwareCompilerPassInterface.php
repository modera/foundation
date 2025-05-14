<?php

namespace Modera\ExpanderBundle\DependencyInjection;

use Modera\ExpanderBundle\Ext\ExtensionPoint;

/**
 * @copyright 2024 Modera Foundation
 */
interface ExtensionPointAwareCompilerPassInterface
{
    /**
     * Must return an instance of extension point that this compiler pass is attached to.
     */
    public function getExtensionPoint(): ?ExtensionPoint;
}

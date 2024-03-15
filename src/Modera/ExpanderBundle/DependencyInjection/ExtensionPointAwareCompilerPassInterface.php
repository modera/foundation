<?php

namespace Modera\ExpanderBundle\DependencyInjection;

use Modera\ExpanderBundle\Ext\ExtensionPoint;

interface ExtensionPointAwareCompilerPassInterface
{
    /**
     * Must return an instance of extension point that this compiler pass is attached to.
     */
    public function getExtensionPoint(): ?ExtensionPoint;
}

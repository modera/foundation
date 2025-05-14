<?php

namespace Modera\ExpanderBundle;

use Modera\ExpanderBundle\Contributing\ExtensionPointsAwareBundlesCollectorCompilerPass;
use Modera\ExpanderBundle\DependencyInjection\DumpExtensionPointsCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @copyright 2024 Modera Foundation
 */
class ModeraExpanderBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new DumpExtensionPointsCompilerPass());
        $container->addCompilerPass(new ExtensionPointsAwareBundlesCollectorCompilerPass());
    }
}

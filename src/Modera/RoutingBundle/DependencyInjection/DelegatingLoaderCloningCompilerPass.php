<?php

namespace Modera\RoutingBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @copyright 2015 Modera Foundation
 */
class DelegatingLoaderCloningCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $container->setDefinition(
            'modera_routing.symfony_delegating_loader',
            clone $container->getDefinition('routing.loader')
        );
    }
}

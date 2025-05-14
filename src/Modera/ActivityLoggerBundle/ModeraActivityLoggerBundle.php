<?php

namespace Modera\ActivityLoggerBundle;

use Modera\ActivityLoggerBundle\DependencyInjection\ServiceAliasCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @copyright 2014 Modera Foundation
 */
class ModeraActivityLoggerBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new ServiceAliasCompilerPass());
    }
}

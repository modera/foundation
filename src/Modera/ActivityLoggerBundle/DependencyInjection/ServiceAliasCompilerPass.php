<?php

namespace Modera\ActivityLoggerBundle\DependencyInjection;

use Modera\ActivityLoggerBundle\Manager\ActivityManagerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Adds a service with ID "modera_activity_logger.manager.activity_manager" to service container
 * that you can use in your application logic without the need to use specific implementation.
 *
 * @copyright 2014 Modera Foundation
 */
class ServiceAliasCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $config = $container->getParameter(ModeraActivityLoggerExtension::CONFIG_KEY);
        if (\is_array($config) && \is_string($config['activity_manager'])) {
            $container->setAlias(ActivityManagerInterface::class, $config['activity_manager']);
            $container->setAlias('modera_activity_logger.manager.activity_manager', $config['activity_manager']);
            $container->getAlias('modera_activity_logger.manager.activity_manager')->setPublic(true);
        }
    }
}

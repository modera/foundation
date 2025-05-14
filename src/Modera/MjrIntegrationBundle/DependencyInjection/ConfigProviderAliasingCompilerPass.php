<?php

namespace Modera\MjrIntegrationBundle\DependencyInjection;

use Modera\MjrIntegrationBundle\Config\MainConfigInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @private
 *
 * @copyright 2016 Modera Foundation
 */
class ConfigProviderAliasingCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        /** @var array{'main_config_provider': string} $semanticConfig */
        $semanticConfig = $container->getParameter(ModeraMjrIntegrationExtension::CONFIG_KEY);

        $container
            ->setAlias(MainConfigInterface::class, $semanticConfig['main_config_provider'])
        ;
        $container
            ->setAlias('modera_mjr_integration.config.main_config', $semanticConfig['main_config_provider'])
            ->setPublic(true)
        ;
    }
}

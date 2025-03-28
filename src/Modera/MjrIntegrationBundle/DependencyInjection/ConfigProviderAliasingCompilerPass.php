<?php

namespace Modera\MjrIntegrationBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @private
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
class ConfigProviderAliasingCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        /** @var array{'main_config_provider': string} $semanticConfig */
        $semanticConfig = $container->getParameter(ModeraMjrIntegrationExtension::CONFIG_KEY);

        $container
            ->setAlias('modera_mjr_integration.config.main_config', $semanticConfig['main_config_provider'])
            ->setPublic(true)
        ;
    }
}

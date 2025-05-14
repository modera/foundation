<?php

namespace Modera\ConfigBundle;

use Modera\ExpanderBundle\Ext\ExtensionPoint;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @copyright 2014 Modera Foundation
 */
class ModeraConfigBundle extends Bundle
{
    public const CONFIG_KEY = 'modera_config.config';

    public function build(ContainerBuilder $container): void
    {
        $configEntriesProvider = new ExtensionPoint('modera_config.config_entries');
        $configEntriesProvider->setDescription(
            'Allow to contribute new configuration properties. See ConfigurationEntryInterface.'
        );
        $container->addCompilerPass($configEntriesProvider->createCompilerPass());

        $listenersExtensionPoint = new ExtensionPoint('modera_config.notification_center_listeners');
        $listenersExtensionPoint->setDescription(
            'Allows you to create listeners to perform custom operations when configuration entry has changes.'
        );

        $container->addCompilerPass($listenersExtensionPoint->createCompilerPass());
    }
}

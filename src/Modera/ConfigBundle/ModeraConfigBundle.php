<?php

namespace Modera\ConfigBundle;

use Sli\ExpanderBundle\Ext\ExtensionPoint;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class ModeraConfigBundle extends Bundle
{
    const CONFIG_KEY = 'modera_config.config';

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $configEntriesProvider = new ExtensionPoint('modera_config.config_entries');
        $configEntriesProvider->setDescription(
            'Allow to contribute new configuration properties. See ConfigurationEntryInterface.'
        );
        $container->addCompilerPass($configEntriesProvider->createCompilerPass());

        $listenersExtensionPoint = new ExtensionPoint('modera_config.notification_center_listeners');
        $listenersExtensionPoint->setSingleContributionTag('modera_config.notification_center_listener');
        $listenersExtensionPoint->setDescription(
            'Allows you to create listeners to perform custom operations when configuration entry has changes.'
        );

        $container->addCompilerPass($listenersExtensionPoint->createCompilerPass());
    }
}

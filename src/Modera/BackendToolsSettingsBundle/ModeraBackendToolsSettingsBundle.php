<?php

namespace Modera\BackendToolsSettingsBundle;

use Modera\ExpanderBundle\Ext\ExtensionPoint;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class ModeraBackendToolsSettingsBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $sectionsProvider = new ExtensionPoint('modera_backend_tools_settings.contributions.sections');
        $sectionsProvider->setDescription('Use this extension point to provide sections to Backend/Tools/Settings section.');

        $container->addCompilerPass($sectionsProvider->createCompilerPass());
    }
}

<?php

namespace Modera\BackendToolsBundle;

use Modera\ExpanderBundle\Ext\ExtensionPoint;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @copyright 2013 Modera Foundation
 */
class ModeraBackendToolsBundle extends Bundle
{
    public const ROLE_ACCESS_TOOLS_SECTION = 'ROLE_BACKEND_TOOLS_ACCESS_SECTION';

    public function build(ContainerBuilder $container): void
    {
        $sectionsProvider = new ExtensionPoint('modera_backend_tools.sections');
        $sectionsProvider->setDescription('Allows to add new sections to Backend/Tools section.');

        $container->addCompilerPass($sectionsProvider->createCompilerPass());
    }
}

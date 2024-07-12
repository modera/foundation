<?php

namespace Modera\BackendTranslationsToolBundle;

use Modera\ExpanderBundle\Ext\ExtensionPoint;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class ModeraBackendTranslationsToolBundle extends Bundle
{
    public const ROLE_ACCESS_BACKEND_TOOLS_TRANSLATIONS_SECTION = 'ROLE_ACCESS_BACKEND_TOOLS_TRANSLATIONS_SECTION';

    public function build(ContainerBuilder $container): void
    {
        $filtersProvider = new ExtensionPoint('modera_backend_translations_tool.filters');
        $filtersProvider->setDescription('Allows to add a new server-side filter (all/new/obsolete filters are implemented using this extension point).');

        $container->addCompilerPass($filtersProvider->createCompilerPass());
    }
}

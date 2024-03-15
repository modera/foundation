<?php

namespace Modera\BackendLanguagesBundle;

use Modera\ExpanderBundle\Contributing\ExtensionPointsAwareBundleInterface;
use Modera\ExpanderBundle\Ext\ExtensionPoint;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class ModeraBackendLanguagesBundle extends Bundle implements ExtensionPointsAwareBundleInterface
{
    public function build(ContainerBuilder $container): void
    {
        $localesExtensionPoint = new ExtensionPoint('modera_backend_languages.locales');
        $localesExtensionPoint->setDescription(
            'Allows to contribute custom locales.'
        );
        $container->addCompilerPass($localesExtensionPoint->createCompilerPass());

        $extUtilFormatResolverExtensionPoint = new ExtensionPoint('modera_backend_languages.ext_util_format_resolver');
        $extUtilFormatResolverExtensionPoint->setDescription(
            'Allows to override default ExtUtilFormat.'
        );
        $container->addCompilerPass($extUtilFormatResolverExtensionPoint->createCompilerPass());
    }

    /**
     * @return array<string, mixed>
     */
    public function getExtensionPointContributions(): array
    {
        return [
            'modera_mjr_integration.css_resources_provider' => [
                '/bundles/moderabackendlanguages/css/styles.css',
            ],
        ];
    }
}

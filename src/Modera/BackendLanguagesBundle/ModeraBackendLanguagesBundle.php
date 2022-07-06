<?php

namespace Modera\BackendLanguagesBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Sli\ExpanderBundle\Contributing\ExtensionPointsAwareBundleInterface;
use Sli\ExpanderBundle\Ext\ExtensionPoint;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class ModeraBackendLanguagesBundle extends Bundle implements ExtensionPointsAwareBundleInterface
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
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
     * {@inheritdoc}
     */
    public function getExtensionPointContributions(): array
    {
        return array(
            'modera_mjr_integration.css_resources_provider' => array(
                '/bundles/moderabackendlanguages/css/styles.css',
            ),
        );
    }
}

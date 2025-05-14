<?php

namespace Modera\BackendLanguagesBundle\Contributions;

use Modera\ExpanderBundle\Ext\AsContributorFor;
use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\MjrIntegrationBundle\DependencyInjection\ModeraMjrIntegrationExtension;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Provides service definitions for client-side dependency injection container.
 *
 * @copyright 2014 Modera Foundation
 */
#[AsContributorFor('modera_mjr_integration.csdi.service_definitions')]
class ClientDiServiceDefinitionsProvider implements ContributorInterface
{
    public function __construct(
        private readonly ParameterBagInterface $parameterBag,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function getItems(): array
    {
        $runtimeConfig = $this->parameterBag->get(ModeraMjrIntegrationExtension::CONFIG_KEY);
        if (!\is_array($runtimeConfig)) {
            $runtimeConfig = [];
        }

        $locale = '__LOCALE__';
        $extJsPath = $runtimeConfig['extjs_path'] ?? '';

        return [
            'extjs_localization_runtime_plugin' => [
                'className' => 'Modera.backend.languages.runtime.ExtJsLocalizationPlugin',
                'tags' => ['runtime_plugin'],
                'args' => [
                    [
                        'urls' => [
                            $extJsPath.'/locale/ext-lang-'.$locale.'.js',
                            $this->urlGenerator->generate('modera_backend_languages_extjs_l10n', ['locale' => $locale]),
                        ],
                    ],
                ],
            ],
            'modera_backend_languages.user_settings_window_contributor' => [
                'className' => 'Modera.backend.languages.runtime.UserSettingsWindowContributor',
                'args' => ['@application'],
                'tags' => ['shared_activities_provider'],
            ],
        ];
    }
}

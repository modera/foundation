<?php

namespace Modera\BackendLanguagesBundle\Contributions;

use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\MjrIntegrationBundle\DependencyInjection\ModeraMjrIntegrationExtension;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides service definitions for client-side dependency injection container.
 *
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class ClientDiServiceDefinitionsProvider implements ContributorInterface
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getItems(): array
    {
        /** @var Router $router */
        $router = $this->container->get('router');

        $runtimeConfig = $this->container->getParameter(ModeraMjrIntegrationExtension::CONFIG_KEY);
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
                            $router->generate('modera_backend_languages_extjs_l10n', ['locale' => $locale]),
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

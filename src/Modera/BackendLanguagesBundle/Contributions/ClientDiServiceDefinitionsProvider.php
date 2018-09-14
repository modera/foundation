<?php

namespace Modera\BackendLanguagesBundle\Contributions;

use Sli\ExpanderBundle\Ext\ContributorInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Modera\MjrIntegrationBundle\DependencyInjection\ModeraMjrIntegrationExtension;

/**
 * Provides service definitions for client-side dependency injection container.
 *
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class ClientDiServiceDefinitionsProvider implements ContributorInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        /* @var Router $router */
        $router = $this->container->get('router');
        $runtimeConfig = $this->container->getParameter(ModeraMjrIntegrationExtension::CONFIG_KEY);
        $locale = '__LOCALE__';

        return array(
            'extjs_localization_runtime_plugin' => array(
                'className' => 'Modera.backend.languages.runtime.ExtJsLocalizationPlugin',
                'tags' => ['runtime_plugin'],
                'args' => array(
                    array(
                        'urls' => array(
                            $runtimeConfig['extjs_path'].'/locale/ext-lang-'.$locale.'.js',
                            $router->generate('modera_backend_languages_extjs_l10n', array('locale' => $locale)),
                        ),
                    ),
                ),
            ),
            'modera_backend_languages.user_settings_window_contributor' => array(
                'className' => 'Modera.backend.languages.runtime.UserSettingsWindowContributor',
                'args' => ['@application'],
                'tags' => ['shared_activities_provider'],
            ),
        );
    }
}

<?php

namespace Modera\MJRSecurityIntegrationBundle\Contributions;

use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\MjrIntegrationBundle\Help\HelpMenuItemInterface;
use Modera\MJRSecurityIntegrationBundle\DependencyInjection\ModeraMJRSecurityIntegrationExtension;
use Modera\SecurityBundle\DependencyInjection\ModeraSecurityExtension;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Firewall\SwitchUserListener;

/**
 * Provides service definitions for client-side dependency injection container.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2013 Modera Foundation
 */
class ServiceDefinitionsProvider implements ContributorInterface
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function getUrl(string $route, array $parameters = []): string
    {
        if ('/' !== $route[0]) {
            /** @var UrlGeneratorInterface $router */
            $router = $this->container->get('router');

            return $router->generate($route, $parameters, UrlGeneratorInterface::ABSOLUTE_PATH);
        }

        return $route;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getSerializedHelpMenuItems(): array
    {
        /** @var ContributorInterface $helpMenuItemsProvider */
        $helpMenuItemsProvider = $this->container->get('modera_mjr_integration.help_menu_items_provider');

        $result = [];
        /** @var HelpMenuItemInterface $item */
        foreach ($helpMenuItemsProvider->getItems() as $item) {
            $result[] = [
                'label' => $item->getLabel(),
                'activityId' => $item->getActivityId(),
                'activityParams' => $item->getActivityParams(),
                'intentId' => $item->getIntentId(),
                'intentParams' => $item->getIntentParams(),
                'url' => $item->getUrl(),
            ];
        }

        return $result;
    }

    public function getItems(): array
    {
        /** @var array{'login_url': string, 'logout_url': string, 'is_authenticated_url': string} $bundleConfig */
        $bundleConfig = $this->container->getParameter(ModeraMJRSecurityIntegrationExtension::CONFIG_KEY);

        $logoutUrl = $this->getUrl($bundleConfig['logout_url']);

        /** @var AuthorizationCheckerInterface $authorizationChecker */
        $authorizationChecker = $this->container->get('security.authorization_checker');
        if ($authorizationChecker->isGranted('ROLE_PREVIOUS_ADMIN')) {
            $switchUserConfig = $this->container->getParameter(ModeraSecurityExtension::CONFIG_KEY.'.switch_user');
            if ($switchUserConfig) {
                $logoutUrl = $this->getUrl('modera_mjr_security_integration.index.switch_user_to', [
                    'username' => SwitchUserListener::EXIT_VALUE,
                ]);
            }
        }

        return [
            'security_manager' => [
                'className' => 'MF.security.AjaxSecurityManager',
                'args' => [
                    [
                        'urls' => [
                            'login' => $this->getUrl($bundleConfig['login_url']),
                            'isAuthenticated' => $this->getUrl($bundleConfig['is_authenticated_url']),
                            'logout' => $logoutUrl,
                        ],
                        'authorizationMgr' => '@authorization_mgr',
                        'interceptor' => '@security_manager_interceptor', // MPFE-922
                    ],
                ],
            ],
            'extdirect_api_script_injector' => [
                'className' => 'Modera.mjrsecurityintegration.runtime.ExtDirectApiScriptInjectorPlugin',
                'args' => [
                    [
                        'directApiUrl' => $this->getUrl('api'),
                    ],
                ],
                'tags' => ['runtime_plugin'],
            ],
            'profile_context_menu' => [
                'className' => 'Modera.mjrsecurityintegration.runtime.ProfileContextMenuPlugin',
                'tags' => ['runtime_plugin'],
            ],
            'modera_backend_security.activation_security_interceptor' => [
                'className' => 'MF.activation.security.ActivationSecurityInterceptor',
                'args' => [
                    [
                        'securityMgr' => '@security_manager',
                    ],
                ],
                'tags' => ['activation_interceptor'],
            ],
            'modera_backend_security.auth_required_delegated_error_handler' => [
                'className' => 'Modera.mjrsecurityintegration.runtime.AuthRequiredDelegatedErrorHandler',
                'args' => [
                    [
                        'exceptionClass' => 'Symfony\Component\Security\Core\Exception\AccessDeniedException',
                        'securityMgr' => '@security_manager',
                    ],
                ],
                'tags' => ['delegated_server_error_handler'],
            ],
            'header_help_button_plugin' => [
                'className' => 'Modera.mjrsecurityintegration.runtime.HeaderHelpButtonPlugin',
                'args' => [
                    [
                        'helpMenuItems' => $this->getSerializedHelpMenuItems(),
                        'workbench' => '@workbench',
                        'intentsMgr' => '@intent_manager',
                    ],
                ],
                'tags' => ['runtime_plugin'],
            ],
        ];
    }
}

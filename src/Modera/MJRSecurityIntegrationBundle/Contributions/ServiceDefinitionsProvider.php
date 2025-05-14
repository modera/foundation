<?php

namespace Modera\MJRSecurityIntegrationBundle\Contributions;

use Modera\ExpanderBundle\Ext\AsContributorFor;
use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\ExpanderBundle\Ext\ExtensionProvider;
use Modera\MjrIntegrationBundle\Help\HelpMenuItemInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Firewall\SwitchUserListener;

/**
 * Provides service definitions for client-side dependency injection container.
 *
 * @copyright 2013 Modera Foundation
 */
#[AsContributorFor('modera_mjr_integration.csdi.service_definitions')]
class ServiceDefinitionsProvider implements ContributorInterface
{
    /**
     * @param array{
     *     'login_url': string,
     *     'logout_url': string,
     *     'is_authenticated_url': string,
     * } $bundleConfig,
     * @param array<string, mixed>|bool $switchUserConfig
     */
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly ExtensionProvider $extensionProvider,
        private readonly array $bundleConfig,
        private readonly array|bool $switchUserConfig,
    ) {
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function getUrl(string $route, array $parameters = []): string
    {
        if ('/' !== $route[0]) {
            return $this->urlGenerator->generate($route, $parameters, UrlGeneratorInterface::ABSOLUTE_PATH);
        }

        return $route;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getSerializedHelpMenuItems(): array
    {
        $helpMenuItemsProvider = $this->extensionProvider->get('modera_mjr_integration.help_menu_items');

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
        $logoutUrl = $this->getUrl($this->bundleConfig['logout_url']);

        if ($this->authorizationChecker->isGranted('ROLE_PREVIOUS_ADMIN')) {
            if ($this->switchUserConfig) {
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
                            'login' => $this->getUrl($this->bundleConfig['login_url']),
                            'isAuthenticated' => $this->getUrl($this->bundleConfig['is_authenticated_url']),
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

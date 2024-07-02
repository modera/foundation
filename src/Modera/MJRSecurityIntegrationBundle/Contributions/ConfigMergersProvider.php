<?php

namespace Modera\MJRSecurityIntegrationBundle\Contributions;

use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\MjrIntegrationBundle\Config\CallbackConfigMerger;
use Modera\SecurityBundle\Entity\UserInterface;
use Modera\SecurityBundle\Security\Authenticator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Provides runtime configuration which should become available after user has authenticated.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class ConfigMergersProvider implements ContributorInterface
{
    private RouterInterface $router;

    private TokenStorageInterface $tokenStorage;

    private ContributorInterface $clientDiDefinitionsProvider;

    /**
     * @var array<string, mixed>
     */
    private array $bundleConfig;

    /**
     * @var array<string, mixed>
     */
    private array $securityConfig;

    /**
     * @var array<string, string[]>
     */
    private array $roleHierarchy;

    /**
     * @param array<string, mixed>    $bundleConfig
     * @param array<string, mixed>    $securityConfig
     * @param array<string, string[]> $roleHierarchy
     */
    public function __construct(
        RouterInterface $router,
        TokenStorageInterface $tokenStorage,
        ContributorInterface $clientDiDefinitionsProvider,
        array $bundleConfig = [],
        array $securityConfig = [],
        array $roleHierarchy = []
    ) {
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
        $this->clientDiDefinitionsProvider = $clientDiDefinitionsProvider;
        $this->bundleConfig = $bundleConfig;
        $this->securityConfig = $securityConfig;
        $this->roleHierarchy = $roleHierarchy;
    }

    public function getItems(): array
    {
        $self = $this;

        $switchUserUrl = null;
        if (isset($this->securityConfig['switch_user']) && $this->securityConfig['switch_user']) {
            /** @var string $switchUserUrl */
            $switchUserUrl = $this->getUrl('modera_mjr_security_integration.index.switch_user_to', [
                'username' => '__username__',
            ]);
        }

        $switchUserListAction = null;
        if (isset($this->bundleConfig['switch_user_list_action']) && $this->bundleConfig['switch_user_list_action']) {
            /** @var string $switchUserListAction */
            $switchUserListAction = $this->bundleConfig['switch_user_list_action'];
        }

        return [
            new CallbackConfigMerger(function (array $currentConfig) use ($self, $switchUserUrl, $switchUserListAction) {
                // we are not making sure that user is authenticated here because we expect that this
                // callback is invoked only when user is already authenticated (invoked from behind a firewall)
                if ($token = $self->tokenStorage->getToken()) {
                    $roles = [];

                    foreach ($token->getRoleNames() as $role) {
                        $roles[] = $role;
                        $roles = \array_merge($roles, $this->findHierarchicalRoles($role, $self->roleHierarchy));
                    }

                    /** @var UserInterface $user */
                    $user = $token->getUser();

                    return \array_merge($currentConfig, [
                        'roles' => \array_values(\array_unique($roles)),
                        'userProfile' => Authenticator::userToArray($user),
                        'switchUserUrl' => $switchUserUrl,
                        'switchUserListAction' => $switchUserListAction,
                    ]);
                } else {
                    return $currentConfig;
                }
            }),
            new CallbackConfigMerger(function (array $currentConfig) use ($self) {
                return \array_merge($currentConfig, [
                    'serviceDefinitions' => $self->clientDiDefinitionsProvider->getItems(),
                ]);
            }),
        ];
    }

    /**
     * @param array<string, string[]> $roleHierarchy
     *
     * @return string[]
     */
    private function findHierarchicalRoles(string $role, array $roleHierarchy): array
    {
        $roles = [];

        if (isset($roleHierarchy[$role])) {
            foreach ($roleHierarchy[$role] as $roleName) {
                $roles[] = $roleName;
                $roles = \array_merge($roles, $this->findHierarchicalRoles($roleName, $roleHierarchy));
            }
        }

        return \array_values(\array_unique($roles));
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function getUrl(string $route, array $parameters = []): string
    {
        if ('/' !== $route[0]) {
            return $this->router->generate($route, $parameters, UrlGeneratorInterface::ABSOLUTE_PATH);
        }

        return $route;
    }
}

<?php

namespace Modera\MJRSecurityIntegrationBundle\Contributions;

use Sli\ExpanderBundle\Ext\ContributorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Modera\MjrIntegrationBundle\Config\CallbackConfigMerger;
use Modera\SecurityBundle\Security\Authenticator;

/**
 * Provides runtime configuration which should become available after user has authenticated.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class ConfigMergersProvider implements ContributorInterface
{
    /**
     * @var array
     */
    private $items;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var ContributorInterface
     */
    private $clientDiDefinitionsProvider;

    /**
     * @var array
     */
    private $bundleConfig;

    /**
     * @var array
     */
    private $securityConfig;

    /**
     * @var array
     */
    private $roleHierarchy;

    /**
     * @param RouterInterface       $router
     * @param TokenStorageInterface $tokenStorage
     * @param ContributorInterface  $clientDiDefinitionsProvider
     * @param array                 $bundleConfig
     * @param array                 $securityConfig
     * @param array                 $roleHierarchy
     */
    public function __construct(
        RouterInterface $router,
        TokenStorageInterface $tokenStorage,
        ContributorInterface $clientDiDefinitionsProvider,
        array $bundleConfig = array(),
        array $securityConfig = array(),
        array $roleHierarchy = array()
    ) {
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
        $this->clientDiDefinitionsProvider = $clientDiDefinitionsProvider;
        $this->bundleConfig = $bundleConfig;
        $this->securityConfig = $securityConfig;
        $this->roleHierarchy = $roleHierarchy;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        $self = $this;

        $switchUserUrl = null;
        if (isset($this->securityConfig['switch_user']) && $this->securityConfig['switch_user']) {
            $switchUserUrl = $this->getUrl('modera_mjr_security_integration.index.switch_user_to', array(
                'username' => '__username__',
            ));
        }

        $switchUserListAction = null;
        if (isset($this->bundleConfig['switch_user_list_action']) && $this->bundleConfig['switch_user_list_action']) {
            $switchUserListAction = $this->bundleConfig['switch_user_list_action'];
        }

        $this->items = array(
            new CallbackConfigMerger(function (array $currentConfig) use ($self, $switchUserUrl, $switchUserListAction) {
                // we are not making sure that user is authenticated here because we expect that this
                // callback is invoked only when user is already authenticated (invoked from behind a firewall)
                if ($token = $self->tokenStorage->getToken()) {
                    $roles = array();

                    foreach ($token->getRoles() as $role) {
                        $roles[] = $role->getRole();
                        $roles = array_merge($roles, $this->findHierarchicalRoles($role->getRole(), $self->roleHierarchy));
                    }

                    return array_merge($currentConfig, array(
                        'roles' => array_values(array_unique($roles)),
                        'userProfile' => Authenticator::userToArray($token->getUser()),
                        'switchUserUrl' => $switchUserUrl,
                        'switchUserListAction' => $switchUserListAction,
                    ));
                } else {
                    return $currentConfig;
                }
            }),
            new CallbackConfigMerger(function (array $currentConfig) use ($self) {
                return array_merge($currentConfig, array(
                    'serviceDefinitions' => $self->clientDiDefinitionsProvider->getItems(),
                ));
            }),
        );

        return $this->items;
    }

    /**
     * @param string $role
     * @param array  $roleHierarchy
     * @return array
     */
    private function findHierarchicalRoles($role, array $roleHierarchy)
    {
        $roles = array();

        if (isset($roleHierarchy[$role])) {
            foreach ($roleHierarchy[$role] as $roleName) {
                $roles[] = $roleName;
                $roles = array_merge($roles, $this->findHierarchicalRoles($roleName, $roleHierarchy));
            }
        }

        return array_values(array_unique($roles));
    }

    /**
     * @param string $route
     * @param array $parameters
     *
     * @return string
     */
    private function getUrl($route, $parameters = [])
    {
        if ('/' !== $route[0]) {
            return $this->router->generate($route, $parameters, UrlGeneratorInterface::ABSOLUTE_PATH);
        }

        return $route;
    }
}

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

        $parameter = '_switch_user';
        if (isset($securityConfig['switch_user']) && is_array($securityConfig['switch_user'])) {
            if (isset($securityConfig['switch_user']['parameter'])) {
                $parameter = $securityConfig['switch_user']['parameter'];
            }
        }
        $switchUserUrl = $this->getUrl(
            isset($bundleConfig['is_authenticated_url']) ? $bundleConfig['is_authenticated_url'] : '/'
        ) . '?' . $parameter . '=';

        $this->items = array(
            new CallbackConfigMerger(function (array $currentConfig) use ($tokenStorage, $roleHierarchy, $switchUserUrl) {
                // we are not making sure that user is authenticated here because we expect that this
                // callback is invoked only when user is already authenticated (invoked from behind a firewall)
                if ($token = $tokenStorage->getToken()) {
                    $roles = array();

                    foreach ($token->getRoles() as $role) {
                        $roles[] = $role->getRole();
                        $roles = array_merge($roles, $this->findHierarchicalRoles($role->getRole(), $roleHierarchy));
                    }

                    return array_merge($currentConfig, array(
                        'roles' => array_values(array_unique($roles)),
                        'userProfile' => Authenticator::userToArray($token->getUser()),
                        'switchUserUrl' => $switchUserUrl,
                    ));
                } else {
                    return $currentConfig;
                }
            }),
            new CallbackConfigMerger(function (array $currentConfig) use ($clientDiDefinitionsProvider) {
                return array_merge($currentConfig, array(
                    'serviceDefinitions' => $clientDiDefinitionsProvider->getItems(),
                ));
            }),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
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
     *
     * @return string
     */
    private function getUrl($route)
    {
        if ('/' !== $route[0]) {
            return $this->router->generate($route, array(), UrlGeneratorInterface::ABSOLUTE_PATH);
        }

        return $route;
    }
}

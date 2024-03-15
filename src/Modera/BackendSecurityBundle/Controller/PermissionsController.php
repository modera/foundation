<?php

namespace Modera\BackendSecurityBundle\Controller;

use Modera\BackendSecurityBundle\ModeraBackendSecurityBundle;
use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\SecurityBundle\Entity\Permission;
use Modera\SecurityBundle\Entity\PermissionCategory;
use Modera\SecurityBundle\Model\PermissionCategoryInterface;
use Modera\SecurityBundle\Model\PermissionInterface;
use Modera\ServerCrudBundle\Controller\AbstractCrudController;
use Modera\ServerCrudBundle\DataMapping\DataMapperInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class PermissionsController extends AbstractCrudController
{
    public function getConfig(): array
    {
        return [
            'entity' => Permission::class,
            'security' => [
                'role' => ModeraBackendSecurityBundle::ROLE_ACCESS_BACKEND_TOOLS_SECURITY_SECTION,
                'actions' => [
                    'create' => false,
                    'remove' => false,
                    'update' => ModeraBackendSecurityBundle::ROLE_MANAGE_PERMISSIONS,
                    'batchUpdate' => false,
                ],
            ],
            'hydration' => [
                'groups' => [
                    'list' => function (Permission $permission) {
                        $users = [];
                        foreach ($permission->getUsers() as $user) {
                            $users[] = $user->getId();
                        }

                        $groups = [];
                        foreach ($permission->getGroups() as $group) {
                            $groups[] = $group->getId();
                        }

                        return [
                            'id' => $permission->getId(),
                            'name' => $this->getPermissionName($permission),
                            'category' => [
                                'id' => $permission->getCategory() ? $permission->getCategory()->getId() : null,
                                'name' => $this->getPermissionCategoryName($permission->getCategory()),
                            ],
                            'users' => $users,
                            'groups' => $groups,
                        ];
                    },
                ],
                'profiles' => [
                    'list',
                ],
            ],
            'map_data_on_update' => function (array $params, Permission $permission, DataMapperInterface $defaultMapper, ContainerInterface $container) {
                $allowedFieldsToEdit = ['users', 'groups'];
                $params = \array_intersect_key($params, \array_flip($allowedFieldsToEdit));
                $defaultMapper->mapData($params, $permission);
            },
        ];
    }

    private function getPermissionCategoryName(?PermissionCategory $entity): ?string
    {
        if (!$entity) {
            return null;
        }

        /** @var PermissionCategoryInterface[] $permissionCategories */
        $permissionCategories = $this->getPermissionCategoriesProvider()->getItems();
        foreach ($permissionCategories as $permissionCategory) {
            if ($permissionCategory->getTechnicalName() === $entity->getTechnicalName()) {
                return $permissionCategory->getName();
            }
        }

        return $entity->getName();
    }

    private function getPermissionName(?Permission $entity): ?string
    {
        if (!$entity) {
            return null;
        }

        /** @var PermissionInterface[] $permissions */
        $permissions = $this->getPermissionsProvider()->getItems();
        foreach ($permissions as $permission) {
            if ($permission->getRole() === $entity->getRole()) {
                return $permission->getName();
            }
        }

        return $entity->getName();
    }

    private function getPermissionCategoriesProvider(): ContributorInterface
    {
        /** @var ContributorInterface $permissionCategoriesProvider */
        $permissionCategoriesProvider = $this->container->get('modera_security.permission_categories_provider');

        return $permissionCategoriesProvider;
    }

    private function getPermissionsProvider(): ContributorInterface
    {
        /** @var ContributorInterface $permissionsProvider */
        $permissionsProvider = $this->container->get('modera_security.permissions_provider');

        return $permissionsProvider;
    }
}

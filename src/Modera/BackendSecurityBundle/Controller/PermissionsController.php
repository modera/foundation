<?php

namespace Modera\BackendSecurityBundle\Controller;

use Modera\BackendSecurityBundle\ModeraBackendSecurityBundle;
use Modera\ExpanderBundle\Ext\ExtensionProvider;
use Modera\SecurityBundle\Entity\Permission;
use Modera\SecurityBundle\Entity\PermissionCategory;
use Modera\SecurityBundle\Model\PermissionCategoryInterface;
use Modera\SecurityBundle\Model\PermissionInterface;
use Modera\ServerCrudBundle\Controller\AbstractCrudController;
use Modera\ServerCrudBundle\DataMapping\DataMapperInterface;
use Symfony\Component\HttpKernel\Attribute\AsController;

/**
 * @copyright 2014 Modera Foundation
 */
#[AsController]
class PermissionsController extends AbstractCrudController
{
    public function __construct(
        private readonly ExtensionProvider $extensionProvider,
    ) {
    }

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
            'map_data_on_update' => function (array $params, Permission $permission, DataMapperInterface $defaultMapper) {
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

        $permissionCategories = $this->getPermissionCategories();
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

        $permissions = $this->getPermissions();
        foreach ($permissions as $permission) {
            if ($permission->getRole() === $entity->getRole()) {
                return $permission->getName();
            }
        }

        return $entity->getName();
    }

    /**
     * @return PermissionCategoryInterface[]
     */
    private function getPermissionCategories(): array
    {
        $id = 'modera_security.permission_categories';
        if ($this->extensionProvider->has($id)) {
            /** @var PermissionCategoryInterface[] $permissionCategories */
            $permissionCategories = $this->extensionProvider->get($id)->getItems();

            return $permissionCategories;
        }

        return [];
    }

    /**
     * @return PermissionInterface[]
     */
    private function getPermissions(): array
    {
        $id = 'modera_security.permissions';
        if ($this->extensionProvider->has($id)) {
            /** @var PermissionInterface[] $permissions */
            $permissions = $this->extensionProvider->get($id)->getItems();

            return $permissions;
        }

        return [];
    }
}

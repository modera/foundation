<?php

namespace Modera\BackendSecurityBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Sli\ExpanderBundle\Ext\ContributorInterface;
use Modera\ServerCrudBundle\DataMapping\DataMapperInterface;
use Modera\ServerCrudBundle\Controller\AbstractCrudController;
use Modera\BackendSecurityBundle\ModeraBackendSecurityBundle;
use Modera\SecurityBundle\Model\PermissionCategoryInterface;
use Modera\SecurityBundle\Model\PermissionInterface;
use Modera\SecurityBundle\Entity\PermissionCategory;
use Modera\SecurityBundle\Entity\Permission;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class PermissionsController extends AbstractCrudController
{
    /**
     * @return array
     */
    public function getConfig(): array
    {
        return array(
            'entity' => Permission::class,
            'security' => array(
                'role' => ModeraBackendSecurityBundle::ROLE_ACCESS_BACKEND_TOOLS_SECURITY_SECTION,
                'actions' => array(
                    'create' => false,
                    'remove' => false,
                    'update' => ModeraBackendSecurityBundle::ROLE_MANAGE_PERMISSIONS,
                    'batchUpdate' => false,
                ),
            ),
            'hydration' => array(
                'groups' => array(
                    'list' => function (Permission $permission) {
                        $users = array();
                        foreach ($permission->getUsers() as $user) {
                            $users[] = $user->getId();
                        }

                        $groups = array();
                        foreach ($permission->getGroups() as $group) {
                            $groups[] = $group->getId();
                        }

                        return array(
                            'id' => $permission->getId(),
                            'name' => $this->getPermissionName($permission),
                            'category' => array(
                                'id' => $permission->getCategory()->getId(),
                                'name' => $this->getPermissionCategoryName($permission->getCategory()),
                            ),
                            'users' => $users,
                            'groups' => $groups,
                        );
                    },
                ),
                'profiles' => array(
                    'list',
                ),
            ),
            'map_data_on_update' => function (array $params, Permission $permission, DataMapperInterface $defaultMapper, ContainerInterface $container) {
                $allowedFieldsToEdit = array('users', 'groups');
                $params = \array_intersect_key($params, \array_flip($allowedFieldsToEdit));
                $defaultMapper->mapData($params, $permission);
            }
        );
    }

    private function getPermissionCategoryName(PermissionCategory $entity): string
    {
        /* @var PermissionCategoryInterface[] $permissionCategories */
        $permissionCategories = $this->getPermissionCategoriesProvider()->getItems();
        foreach ($permissionCategories as $permissionCategory) {
            if ($permissionCategory->getTechnicalName() === $entity->getTechnicalName()) {
                return $permissionCategory->getName();
            }
        }
        return $entity->getName();
    }

    private function getPermissionName(Permission $entity): string
    {
        /* @var PermissionInterface[] $permissions */
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
        return $this->get('modera_security.permission_categories_provider');
    }

    private function getPermissionsProvider(): ContributorInterface
    {
        return $this->get('modera_security.permissions_provider');
    }
}

<?php

namespace Modera\SecurityBundle\DataInstallation;

use Doctrine\ORM\EntityManagerInterface;
use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\SecurityBundle\Entity\Permission;
use Modera\SecurityBundle\Entity\PermissionCategory;
use Modera\SecurityBundle\Model\PermissionCategoryInterface;
use Modera\SecurityBundle\Model\PermissionInterface;

/**
 * Service responsible for installing permissions and permission categories so later they can be used to manage
 * user permissions.
 *
 * @copyright 2014 Modera Foundation
 */
class PermissionAndCategoriesInstaller
{
    /**
     * @var array{'categories': array<string, int>, 'permissions': array<string, int>}
     */
    private array $sortingPosition;

    /**
     * @internal
     *
     * @param array{'categories'?: array<string, int>, 'permissions'?: array<string, int>} $sortingPosition
     */
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ContributorInterface $permissionCategoriesProvider,
        private readonly ContributorInterface $permissionsProvider,
        array $sortingPosition = [],
    ) {
        $this->sortingPosition = \array_merge([
            'categories' => [],
            'permissions' => [],
        ], $sortingPosition);
    }

    /**
     * @return array{'installed': int}
     */
    public function installCategories(): array
    {
        $permissionCategoriesInstalled = 0;
        $sortingPosition = $this->sortingPosition['categories'];

        /** @var PermissionCategoryInterface[] $permissionCategories */
        $permissionCategories = $this->permissionCategoriesProvider->getItems();
        if (\count($permissionCategories) > 0) {
            foreach ($permissionCategories as $permissionCategory) {
                /** @var ?PermissionCategory $entityPermissionCategory */
                $entityPermissionCategory = $this->em->getRepository(PermissionCategory::class)->findOneBy([
                    'technicalName' => $permissionCategory->getTechnicalName(),
                ]);

                if (!$entityPermissionCategory) {
                    $entityPermissionCategory = new PermissionCategory();
                    $entityPermissionCategory->setTechnicalName($permissionCategory->getTechnicalName());

                    $this->em->persist($entityPermissionCategory);

                    ++$permissionCategoriesInstalled;
                }

                $entityPermissionCategory->setName($permissionCategory->getName());

                $position = 0;
                if (isset($sortingPosition[$entityPermissionCategory->getTechnicalName()])) {
                    $position = $sortingPosition[$entityPermissionCategory->getTechnicalName()];
                }
                $entityPermissionCategory->setPosition($position);
            }
        }

        $this->em->flush();

        return [
            'installed' => $permissionCategoriesInstalled,
            // 'removed' => 0,
        ];
    }

    /**
     * @return array{'installed': int}
     */
    public function installPermissions(): array
    {
        $permissionInstalled = 0;
        $sortingPosition = $this->sortingPosition['permissions'];

        /** @var PermissionInterface[] $permissions */
        $permissions = $this->permissionsProvider->getItems();
        foreach ($permissions as $permission) {
            $entityPermission = $this->em->getRepository(Permission::class)->findOneBy([
                'roleName' => $permission->getRole(),
            ]);

            if (!$entityPermission) {
                $entityPermission = new Permission();
                $entityPermission->setRoleName($permission->getRole());

                $this->em->persist($entityPermission);

                ++$permissionInstalled;
            }

            $entityPermission->setDescription($permission->getDescription());
            $entityPermission->setName($permission->getName());

            $position = 0;
            if (isset($sortingPosition[$entityPermission->getRoleName()])) {
                $position = $sortingPosition[$entityPermission->getRoleName()];
            }
            $entityPermission->setPosition($position);

            $categoryTechnicalName = $permission->getCategory();

            /** @var ?PermissionCategory $category */
            $category = $this->em->getRepository(PermissionCategory::class)->findOneBy([
                'technicalName' => $categoryTechnicalName,
            ]);
            if ($category) {
                $entityPermission->setCategory($category);
            }
        }

        $this->em->flush();

        return [
            'installed' => $permissionInstalled,
            // 'removed' => 0,
        ];
    }
}

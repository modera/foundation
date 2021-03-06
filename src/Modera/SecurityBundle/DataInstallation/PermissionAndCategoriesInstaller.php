<?php

namespace Modera\SecurityBundle\DataInstallation;

use Doctrine\ORM\EntityManager;
use Modera\FoundationBundle\Utils\DeprecationNoticeEmitter;
use Modera\SecurityBundle\Entity\Permission;
use Modera\SecurityBundle\Entity\PermissionCategory;
use Modera\SecurityBundle\Model\PermissionCategoryInterface;
use Sli\ExpanderBundle\Ext\ContributorInterface;

/**
 * Service responsible for installing permissions and permission categories so later they can be used to manage
 * user permissions.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class PermissionAndCategoriesInstaller
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var ContributorInterface
     */
    private $permissionCategoriesProvider;

    /**
     * @var ContributorInterface
     */
    private $permissionsProvider;

    /**
     * @var BCLayer
     */
    private $bcLayer;

    /**
     * @var DeprecationNoticeEmitter
     */
    private $deprecationNoticeEmitter;

    /**
     * @internal Since 2.54.0
     *
     * @param EntityManager            $em
     * @param ContributorInterface     $permissionCategoriesProvider
     * @param ContributorInterface     $permissionsProvider
     * @param BCLayer                  $bcLayer
     * @param DeprecationNoticeEmitter $deprecationNoticeEmitter
     */
    public function __construct(
        EntityManager $em,
        ContributorInterface $permissionCategoriesProvider,
        ContributorInterface $permissionsProvider,
        BCLayer $bcLayer = null,
        DeprecationNoticeEmitter $deprecationNoticeEmitter = null
    ) {
        $this->em = $em;
        $this->permissionCategoriesProvider = $permissionCategoriesProvider;
        $this->permissionsProvider = $permissionsProvider;
        $this->bcLayer = $bcLayer;
        $this->deprecationNoticeEmitter = $deprecationNoticeEmitter;
    }

    /**
     * @return array
     */
    public function installCategories()
    {
        $permissionCategoriesInstalled = 0;

        /* @var PermissionCategoryInterface[] $permissionCategories */
        $permissionCategories = $this->permissionCategoriesProvider->getItems();
        if (count($permissionCategories) > 0) {
            foreach ($permissionCategories as $permissionCategory) {
                /* @var PermissionCategory $entityPermissionCategory */
                $entityPermissionCategory = $this->em->getRepository(PermissionCategory::clazz())->findOneBy(array(
                    'technicalName' => $permissionCategory->getTechnicalName(),
                ));

                if (!$entityPermissionCategory) {
                    $entityPermissionCategory = new PermissionCategory();
                    $entityPermissionCategory->setTechnicalName($permissionCategory->getTechnicalName());

                    $this->em->persist($entityPermissionCategory);

                    ++$permissionCategoriesInstalled;
                }

                $entityPermissionCategory->setName($permissionCategory->getName());
            }
        }

        $this->em->flush();

        if ($this->bcLayer) {
            $this->bcLayer->syncPermissionCategoryTechnicalNamesInDatabase();
        }

        return array(
            'installed' => $permissionCategoriesInstalled,
            'removed' => 0,
        );
    }

    /**
     * @return array
     */
    public function installPermissions()
    {
        $permissionInstalled = 0;

        $permissions = $this->permissionsProvider->getItems();
        foreach ($permissions as $permission) {
            /* @var \Modera\SecurityBundle\Model\PermissionInterface $permission */
            $entityPermission = $this->em->getRepository(Permission::clazz())->findOneBy(array(
                'roleName' => $permission->getRole(),
            ));

            if (!$entityPermission) {
                $entityPermission = new Permission();
                $entityPermission->setRoleName($permission->getRole());

                $this->em->persist($entityPermission);

                ++$permissionInstalled;
            }

            $entityPermission->setDescription($permission->getDescription());
            $entityPermission->setName($permission->getName());

            $categoryTechnicalName = $permission->getCategory();
            if ($this->bcLayer) {
                // MPFE-964, see \Modera\BackendSecurityBundle\Contributions\PermissionCategoriesProvider
                $newCategoryName = $this->bcLayer->resolveNewPermissionCategoryTechnicalName($categoryTechnicalName);
                if (false !== $newCategoryName) {
                    $this->emitDeprecationNotice(sprintf(
                        'Permission category "%s" is deprecated, you must use "%s" category instead when contributing new permissions.',
                        $categoryTechnicalName, $newCategoryName
                    ));

                    $categoryTechnicalName = $newCategoryName;
                }
            }

            /* @var PermissionCategory $category */
            $category = $this->em->getRepository(PermissionCategory::clazz())->findOneBy(array(
                'technicalName' => $categoryTechnicalName,
            ));
            if ($category) {
                $entityPermission->setCategory($category);
            }
        }

        $this->em->flush();

        return array(
            'installed' => $permissionInstalled,
            'removed' => 0,
        );
    }

    private function emitDeprecationNotice($notice)
    {
        if ($this->deprecationNoticeEmitter) {
            $this->deprecationNoticeEmitter->emit($notice);
        }
    }
}

<?php

namespace Modera\SecurityBundle\Tests\Functional\DataInstallation;

use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\SecurityBundle\DataInstallation\PermissionAndCategoriesInstaller;
use Modera\SecurityBundle\Entity\Permission as PermissionEntity;
use Modera\SecurityBundle\Entity\PermissionCategory as PermissionCategoryEntity;
use Modera\SecurityBundle\Model\Permission;
use Modera\SecurityBundle\Model\PermissionCategory;

class PermissionAndCategoriesInstallerTest extends AbstractTestCase
{
    private PermissionAndCategoriesInstaller $installer;

    private ContributorInterface $permissionCategoriesProvider;

    private ContributorInterface $permissionsProvider;

    public function doSetUp(): void
    {
        $this->permissionCategoriesProvider = $this->createMock(ContributorInterface::class);
        $this->permissionsProvider = $this->createMock(ContributorInterface::class);

        $this->installer = new PermissionAndCategoriesInstaller(
            self::$em,
            $this->permissionCategoriesProvider,
            $this->permissionsProvider,
        );
    }

    private function getLastRecordInDatabase($entityClass): ?object
    {
        $query = self::$em->createQuery(sprintf('SELECT e FROM %s e ORDER BY e.id DESC', $entityClass));
        $query->setMaxResults(1);

        return $query->getSingleResult();
    }

    private function assertValidResultStructure(array $result): void
    {
        $this->assertTrue(\is_array($result));
        $this->assertArrayHasKey('installed', $result);
        // $this->assertArrayHasKey('removed', $result);
    }

    public function testInstallCategories(): void
    {
        $category = new PermissionCategory('foo category', 'foo_category');

        $pcp = $this->permissionCategoriesProvider;
        $pcp->expects($this->atLeastOnce())
            ->method('getItems')
            ->will($this->returnValue([$category]));

        $result = $this->installer->installCategories();

        $this->assertValidResultStructure($result);
        $this->assertEquals(1, $result['installed']);
        // $this->assertEquals(0, $result['removed']);

        /** @var PermissionCategoryEntity $installedCategory */
        $installedCategory = $this->getLastRecordInDatabase(PermissionCategoryEntity::class);

        $this->assertNotNull($installedCategory);
        $this->assertEquals($category->getName(), $installedCategory->getName());
        $this->assertEquals($category->getTechnicalName(), $installedCategory->getTechnicalName());

        // ---

        $result = $this->installer->installCategories();

        $this->assertValidResultStructure($result);
        $this->assertEquals(0, $result['installed']);
        // $this->assertEquals(0, $result['removed']);
    }

    public function testInstallPermission(): void
    {
        /** @var PermissionCategoryEntity $installedCategory */
        $category = $this->getLastRecordInDatabase(PermissionCategoryEntity::class);

        $permission = new Permission('foo name', 'FOO_ROLE', $category->getTechnicalName(), 'foo description');

        $pp = $this->permissionsProvider;
        $pp->expects($this->atLeastOnce())
           ->method('getItems')
           ->will($this->returnValue([$permission]));

        $result = $this->installer->installPermissions();

        $this->assertValidResultStructure($result);
        $this->assertEquals(1, $result['installed']);
        // $this->assertEquals(0, $result['removed']);

        /** @var PermissionEntity $installedPermission */
        $installedPermission = $this->getLastRecordInDatabase(PermissionEntity::class);

        $this->assertNotNull($installedPermission);
        $this->assertEquals($permission->getName(), $installedPermission->getName());
        $this->assertEquals($permission->getDescription(), $installedPermission->getDescription());
        $this->assertEquals($permission->getRole(), $installedPermission->getRole());
        $this->assertNotNull($installedPermission->getCategory());
        $this->assertEquals($category->getId(), $installedPermission->getCategory()->getId());

        // ---

        $result = $this->installer->installPermissions();

        $this->assertValidResultStructure($result);
        $this->assertEquals(0, $result['installed']);
        // $this->assertEquals(0, $result['removed']);
    }
}

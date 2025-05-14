<?php

namespace Modera\SecurityBundle\Tests\Unit\RootUserHandler;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ObjectRepository;
use Modera\SecurityBundle\Entity\Permission;
use Modera\SecurityBundle\Entity\User;
use Modera\SecurityBundle\ModeraSecurityBundle;
use Modera\SecurityBundle\RootUserHandling\SemanticConfigRootUserHandler;

class SemanticConfigRootUserHandlerTest extends \PHPUnit\Framework\TestCase
{
    public function testIsRootUser(): void
    {
        $bundleConfig = [
            'root_user' => [
                'query' => ['dat', 'is', 'query'],
            ],
        ];

        $em = \Phake::mock(EntityManagerInterface::class);

        $handler = new SemanticConfigRootUserHandler($em, $bundleConfig);

        $anonymousUser = \Phake::mock(User::class);
        $rootUser = \Phake::mock(User::class);

        $dbUser = \Phake::mock(User::class);
        \Phake::when($dbUser)->isEqualTo($anonymousUser)->thenReturn(false);
        \Phake::when($dbUser)->isEqualTo($rootUser)->thenReturn(true);

        $userRepository = \Phake::mock(ObjectRepository::class);
        \Phake::when($userRepository)->findOneBy($bundleConfig['root_user']['query'])->thenReturn($dbUser);
        \Phake::when($em)->getRepository(User::class)->thenReturn($userRepository);

        $this->assertFalse($handler->isRootUser($anonymousUser));
        $this->assertTrue($handler->isRootUser($rootUser));
    }

    public function testGetRolesWithAsterisk(): void
    {
        $em = \Phake::mock(EntityManagerInterface::class);

        $bundleConfig = [
            'root_user' => [
                'roles' => '*',
            ],
        ];

        $databaseRoles = [
            ['roleName' => 'FOO_ROLE'],
            ['roleName' => 'BAR_ROLE'],
        ];

        $query = \Phake::mock(AbstractQuery::class);
        \Phake::when($em)->createQuery(\sprintf('SELECT e.roleName FROM %s e', Permission::class))->thenReturn($query);
        \Phake::when($query)->getResult(Query::HYDRATE_SCALAR)->thenReturn($databaseRoles);

        $handler = new SemanticConfigRootUserHandler($em, $bundleConfig);

        $this->assertSame(['FOO_ROLE', 'BAR_ROLE', ModeraSecurityBundle::ROLE_ROOT_USER], $handler->getRoles());
    }

    public function testGetRolesAsArray(): void
    {
        $em = \Phake::mock(EntityManagerInterface::class);

        $bundleConfig = [
            'root_user' => [
                'roles' => ['FOO_ROLE', 'BAR_ROLE'],
            ],
        ];

        $handler = new SemanticConfigRootUserHandler($em, $bundleConfig);

        $expected = \array_merge($bundleConfig['root_user']['roles'], [ModeraSecurityBundle::ROLE_ROOT_USER]);
        $this->assertSame($expected, $handler->getRoles());
    }

    public function testGetRolesNeitherStringNorArrayDefined(): void
    {
        $this->expectException(\RuntimeException::class);

        $em = \Phake::mock(EntityManagerInterface::class);

        $bundleConfig = [
            'root_user' => [
                'roles' => new \stdClass(),
            ],
        ];

        $handler = new SemanticConfigRootUserHandler($em, $bundleConfig);

        $handler->getRoles();
    }
}

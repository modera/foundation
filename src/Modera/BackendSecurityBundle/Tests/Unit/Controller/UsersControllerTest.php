<?php

namespace Modera\BackendSecurityBundle\Tests\Unit\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Modera\ActivityLoggerBundle\Manager\ActivityManagerInterface;
use Modera\BackendSecurityBundle\Controller\UsersController;
use Modera\BackendSecurityBundle\ModeraBackendSecurityBundle;
use Modera\SecurityBundle\Entity\User;
use Modera\SecurityBundle\PasswordStrength\PasswordManager;
use Modera\SecurityBundle\Service\UserService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UsersControllerTest extends \PHPUnit\Framework\TestCase
{
    public function testGeneratePasswordActionForAuthenticatedUser(): void
    {
        $userMock = \Phake::mock(User::class);

        $tokenMock = \Phake::mock(UsernamePasswordToken::class);
        \Phake::when($tokenMock)
            ->getUser()
            ->thenReturn($userMock)
        ;

        $tokenStorageMock = \Phake::mock(TokenStorageInterface::class);
        \Phake::when($tokenStorageMock)
            ->getToken()
            ->thenReturn($tokenMock)
        ;

        $passwordManager = \Phake::mock(PasswordManager::class);
        \Phake::when($passwordManager)
            ->generatePassword($userMock)
            ->thenReturn('foo-pwd')
        ;
        $authCheckerMock = \Phake::mock(AuthorizationCheckerInterface::class);
        \Phake::when($authCheckerMock)
            ->isGranted(ModeraBackendSecurityBundle::ROLE_MANAGE_USER_PROFILES, $this->anything())
            ->thenReturn(true)
        ;
        $containerMock = \Phake::mock(ContainerInterface::class);
        \Phake::when($containerMock)
            ->get(PasswordManager::class)
            ->thenReturn($passwordManager)
        ;

        \Phake::when($containerMock)
            ->has('security.token_storage')
            ->thenReturn(true)
        ;

        \Phake::when($containerMock)
            ->get('security.token_storage')
            ->thenReturn($tokenStorageMock)
        ;

        $controller = new UsersController(
            \Phake::mock(ActivityManagerInterface::class),
            \Phake::mock(EntityManagerInterface::class),
            $passwordManager,
            \Phake::mock(UserService::class),
        );
        $controller->setContainer($containerMock);

        $result = $controller->generatePasswordAction([]);

        $expectedResult = [
            'success' => true,
            'result' => [
                'plainPassword' => 'foo-pwd',
            ],
        ];
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Because $authenticatedUser != $anotherUser and no admin privileges.
     */
    public function testGeneratePasswordActionWhenIdExplicitlyProvidedNoMatchNotAdmin(): void
    {
        $this->expectException(AccessDeniedException::class);
        $authenticatedUser = \Phake::mock(User::class);

        $tokenMock = \Phake::mock(UsernamePasswordToken::class);
        \Phake::when($tokenMock)
            ->getUser()
            ->thenReturn($authenticatedUser)
        ;

        $tokenStorageMock = \Phake::mock(TokenStorageInterface::class);
        \Phake::when($tokenStorageMock)
            ->getToken()
            ->thenReturn($tokenMock)
        ;
        $authCheckerMock = \Phake::mock(AuthorizationCheckerInterface::class);
        \Phake::when($authCheckerMock)
            ->isGranted(ModeraBackendSecurityBundle::ROLE_MANAGE_USER_PROFILES, $this->anything())
            ->thenReturn(false)
        ;
        $containerMock = \Phake::mock(ContainerInterface::class);

        \Phake::when($containerMock)
            ->has('security.authorization_checker')
            ->thenReturn(true)
        ;
        \Phake::when($containerMock)
            ->get('security.authorization_checker')
            ->thenReturn($authCheckerMock)
        ;

        \Phake::when($containerMock)
            ->has('security.token_storage')
            ->thenReturn(true)
        ;
        \Phake::when($containerMock)
            ->get('security.token_storage')
            ->thenReturn($tokenStorageMock)
        ;

        $anotherUser = \Phake::mock(User::class);
        \Phake::when($anotherUser)
            ->getUsername()
            ->thenReturn('another-user')
        ;

        $userRepositoryMock = \Phake::mock(ObjectRepository::class);
        \Phake::when($userRepositoryMock)
            ->find(123)
            ->thenReturn($anotherUser)
        ;

        $doctrineMock = \Phake::mock(EntityManagerInterface::class);
        \Phake::when($doctrineMock)
            ->getRepository(User::class)
            ->thenReturn($userRepositoryMock)
        ;

        \Phake::when($containerMock)
            ->has('doctrine.orm.entity_manager')
            ->thenReturn(true)
        ;
        \Phake::when($containerMock)
            ->get('doctrine.orm.entity_manager')
            ->thenReturn($doctrineMock)
        ;

        $controller = new UsersController(
            \Phake::mock(ActivityManagerInterface::class),
            $doctrineMock,
            \Phake::mock(PasswordManager::class),
            \Phake::mock(UserService::class),
        );
        $controller->setContainer($containerMock);

        $result = $controller->generatePasswordAction(['userId' => 123]);

        $expectedResult = [
            'success' => true,
            'result' => [
                'plainPassword' => 'foo-pwd',
            ],
        ];
        $this->assertSame($expectedResult, $result);
    }
}

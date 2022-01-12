<?php

namespace Modera\BackendSecurityBundle\Tests\Unit\Controller;

use Doctrine\Persistence\ObjectRepository;
use Modera\BackendSecurityBundle\Controller\UsersController;
use Modera\BackendSecurityBundle\ModeraBackendSecurityBundle;
use Modera\SecurityBundle\Entity\User;
use Modera\SecurityBundle\PasswordStrength\PasswordGenerator;
use Modera\SecurityBundle\PasswordStrength\PasswordManager;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Refactored to be a Unit test.
 *
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class UsersControllerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var UsersController
     */
    private $controller;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->controller = new UsersController();
    }

    public function testGeneratePasswordAction_forAuthenticatedUser()
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
            ->get('modera_security.password_strength.password_manager')
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

        $this->controller->setContainer($containerMock);

        $result = $this->controller->generatePasswordAction(array());

        $expectedResult = array(
            'success' => true,
            'result' => array(
                'plainPassword' => 'foo-pwd',
            ),
        );
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Because $authenticatedUser != $anotherUser and no admin privileges
     *
     * @expectedException Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function testGeneratePasswordAction_whenIdExplicitlyProvided_noMatch_notAdmin()
    {
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

        $doctrineMock = \Phake::mock(ManagerRegistry::class);
        \Phake::when($doctrineMock)
            ->getRepository(User::class)
            ->thenReturn($userRepositoryMock)
        ;

        \Phake::when($containerMock)
            ->has('doctrine')
            ->thenReturn(true)
        ;
        \Phake::when($containerMock)
            ->get('doctrine')
            ->thenReturn($doctrineMock)
        ;

        $this->controller->setContainer($containerMock);

        $result = $this->controller->generatePasswordAction(array('userId' => 123));
    }
}

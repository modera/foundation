<?php

namespace Modera\BackendSecurityBundle\Tests\Unit\Controller;

use Modera\BackendSecurityBundle\Controller\UsersController;
use Modera\BackendSecurityBundle\ModeraBackendSecurityBundle;
use Modera\SecurityBundle\PasswordStrength\PasswordGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Refactored to be a Unit test.
 *
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class UsersControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UsersController
     */
    private $controller;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->controller = new UsersController();
    }

    public function testGeneratePasswordAction()
    {
        $passwordGeneratorMock = \Phake::mock(PasswordGenerator::class);
        \Phake::when($passwordGeneratorMock)
            ->generatePassword()
            ->thenReturn('foo-pwd')
        ;
        $authCheckerMock = \Phake::mock(AuthorizationCheckerInterface::class);
        \Phake::when($authCheckerMock)
            ->isGranted(ModeraBackendSecurityBundle::ROLE_MANAGE_USER_PROFILES, $this->anything())
            ->thenReturn(true)
        ;
        $containerMock = \Phake::mock(ContainerInterface::class);
        \Phake::when($containerMock)
            ->get('modera_security.password_strength.password_generator')
            ->thenReturn($passwordGeneratorMock)
        ;
        \Phake::when($containerMock)
            ->has('security.authorization_checker')
            ->thenReturn(true)
        ;
        \Phake::when($containerMock)
            ->get('security.authorization_checker')
            ->thenReturn($authCheckerMock)
        ;

        $this->controller->setContainer($containerMock);

        $result = $this->controller->generatePasswordAction(array());
        $this->assertTrue(is_array($result));
        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('result', $result);
        $this->assertTrue(is_array($result['result']));
        $this->assertEquals(1, count($result['result']));
        $this->assertArrayHasKey('plainPassword', $result['result']);
        $this->assertEquals('foo-pwd', $result['result']['plainPassword']);
    }
}

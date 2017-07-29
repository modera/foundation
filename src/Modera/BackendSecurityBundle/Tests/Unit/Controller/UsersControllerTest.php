<?php

namespace Modera\BackendSecurityBundle\Tests\Unit\Controller;

use Modera\BackendSecurityBundle\Controller\UsersController;
use Modera\SecurityBundle\PasswordStrength\PasswordGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
        $containerMock = \Phake::mock(ContainerInterface::class);
        \Phake::when($containerMock)
            ->get('modera_security.password_strength.password_generator')
            ->thenReturn($passwordGeneratorMock)
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

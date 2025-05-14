<?php

namespace Modera\MJRSecurityIntegrationBundle\Tests\Unit\Contributions;

use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\MjrIntegrationBundle\Config\ConfigMergerInterface;
use Modera\MJRSecurityIntegrationBundle\Contributions\ConfigMergersProvider;
use Modera\SecurityBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ConfigMergersProviderTest extends \PHPUnit\Framework\TestCase
{
    public function testSecurityRolesMerger(): void
    {
        $roles = [
            'ROLE_USER',
            'ROLE_ADMIN',
        ];

        $user = \Phake::mock(User::class);
        \Phake::when($user)->getId()->thenReturn(777);
        \Phake::when($user)->getFullName()->thenReturn('John Doe');
        \Phake::when($user)->getEmail()->thenReturn('john.doe@example.org');
        \Phake::when($user)->getUserIdentifier()->thenReturn('john.doe');
        \Phake::when($user)->getUsername()->thenReturn('john.doe');

        $token = \Phake::mock(TokenInterface::class);
        \Phake::when($token)
            ->getUser()
            ->thenReturn($user)
        ;
        \Phake::when($token)
            ->getRoleNames()
            ->thenReturn($roles)
        ;

        $serviceDefinitions = [
            'fooService',
            'barService',
        ];

        $clientDiDefinitionsProvider = $this->createMock(ContributorInterface::class);
        $clientDiDefinitionsProvider->expects($this->atLeastOnce())
            ->method('getItems')
            ->will($this->returnValue($serviceDefinitions));

        $router = \Phake::mock('Symfony\Component\Routing\RouterInterface');
        \Phake::when($router)
            ->generate(\Phake::anyParameters())
            ->thenReturn('')
        ;

        $tokenStorage = \Phake::mock('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface');
        \Phake::when($tokenStorage)
            ->getToken()
            ->thenReturn($token)
        ;

        $provider = new ConfigMergersProvider($router, $tokenStorage, $clientDiDefinitionsProvider);
        $mergers = $provider->getItems();

        $this->assertEquals(2, \count($mergers));

        /** @var ConfigMergerInterface $securityRolesMerger */
        $securityRolesMerger = $mergers[0];
        /** @var ConfigMergerInterface $clientDiDefinitionsProviderMerger */
        $clientDiDefinitionsProviderMerger = $mergers[1];

        $this->assertInstanceOf('Modera\MjrIntegrationBundle\Config\ConfigMergerInterface', $securityRolesMerger);
        $this->assertInstanceOf('Modera\MjrIntegrationBundle\Config\ConfigMergerInterface', $clientDiDefinitionsProviderMerger);

        $existingConfig = [
            'something' => 'blah',
        ];
        $mergedConfig = $securityRolesMerger->merge($existingConfig);

        $this->assertTrue(\is_array($mergedConfig));
        $this->assertArrayHasKey('something', $mergedConfig);
        $this->assertEquals('blah', $mergedConfig['something']);
        $this->assertArrayHasKey('roles', $mergedConfig);
        $this->assertTrue(is_array($mergedConfig['roles']));
        $this->assertEquals(2, \count($mergedConfig['roles']));
        $this->assertTrue(in_array('ROLE_USER', $mergedConfig['roles']));
        $this->assertTrue(in_array('ROLE_ADMIN', $mergedConfig['roles']));
        $this->assertArrayHasKey('userProfile', $mergedConfig);
        $this->assertTrue(is_array($mergedConfig['userProfile']));
        $this->assertArrayHasKey('id', $mergedConfig['userProfile']);
        $this->assertArrayHasKey('name', $mergedConfig['userProfile']);
        $this->assertArrayHasKey('email', $mergedConfig['userProfile']);
        $this->assertArrayHasKey('username', $mergedConfig['userProfile']);
        $this->assertEquals(777, $mergedConfig['userProfile']['id']);
        $this->assertEquals('John Doe', $mergedConfig['userProfile']['name']);
        $this->assertEquals('john.doe@example.org', $mergedConfig['userProfile']['email']);
        $this->assertEquals('john.doe', $mergedConfig['userProfile']['username']);
        $this->assertArrayHasKey('switchUserUrl', $mergedConfig);
        $this->assertEquals(null, $mergedConfig['switchUserUrl']);

        $mergedConfig = $clientDiDefinitionsProviderMerger->merge($existingConfig);

        $this->assertArrayHasKey('serviceDefinitions', $mergedConfig);
        $this->assertTrue(is_array($mergedConfig['serviceDefinitions']));
        $this->assertEquals(2, \count($mergedConfig['serviceDefinitions']));
        $this->assertTrue(in_array('fooService', $mergedConfig['serviceDefinitions']));
        $this->assertTrue(in_array('barService', $mergedConfig['serviceDefinitions']));
    }
}

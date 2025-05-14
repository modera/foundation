<?php

namespace Modera\MJRSecurityIntegrationBundle\Tests\Unit\DependencyInjection;

use Modera\MJRSecurityIntegrationBundle\DependencyInjection\ModeraMJRSecurityIntegrationExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ModeraMJRSecurityIntegrationExtensionTest extends \PHPUnit\Framework\TestCase
{
    public function testLoad(): void
    {
        $semanticConfig = [
            'modera_mjr_security_integration' => [
                'login_url' => '/login',
                'logout_url' => '/logout',
                'is_authenticated_url' => '/isAuthenticated',
            ],
        ];

        $builder = new ContainerBuilder();

        $ext = new ModeraMJRSecurityIntegrationExtension();
        $ext->load($semanticConfig, $builder);

        $injectedSemanticConfig = $builder->getParameter(ModeraMJRSecurityIntegrationExtension::CONFIG_KEY);
        foreach ($semanticConfig['modera_mjr_security_integration'] as $key => $value) {
            $this->assertArrayHasKey($key, $injectedSemanticConfig);
            $this->assertEquals($value, $injectedSemanticConfig[$key]);
        }
    }
}

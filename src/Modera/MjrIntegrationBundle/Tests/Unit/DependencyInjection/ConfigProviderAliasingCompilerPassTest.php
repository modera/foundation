<?php

namespace Modera\MjrIntegrationBundle\Tests\Unit\DependencyInjection;

use Modera\MjrIntegrationBundle\DependencyInjection\ConfigProviderAliasingCompilerPass;
use Modera\MjrIntegrationBundle\DependencyInjection\ModeraMjrIntegrationExtension;

class ConfigProviderAliasingCompilerPassTest extends \PHPUnit\Framework\TestCase
{
    public function testProcess(): void
    {
        $alias = \Phake::mock('Symfony\Component\DependencyInjection\Alias');
        \Phake::when($alias)
            ->setPublic(\Phake::anyParameters())
            ->thenReturn($alias)
        ;

        $container = \Phake::mock('Symfony\Component\DependencyInjection\ContainerBuilder');
        \Phake::when($container)
            ->getParameter(ModeraMjrIntegrationExtension::CONFIG_KEY)
            ->thenReturn([
                'main_config_provider' => 'foo_service',
            ])
        ;
        \Phake::when($container)
            ->setAlias(\Phake::anyParameters())
            ->thenReturn($alias)
        ;

        $pass = new ConfigProviderAliasingCompilerPass();
        $pass->process($container);

        \Phake::verify($container)
            ->setAlias('modera_mjr_integration.config.main_config', 'foo_service')
        ;
    }
}

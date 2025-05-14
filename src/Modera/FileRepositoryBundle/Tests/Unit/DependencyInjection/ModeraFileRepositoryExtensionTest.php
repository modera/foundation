<?php

namespace Modera\FileRepositoryBundle\Tests\Unit\DependencyInjection;

use Modera\FileRepositoryBundle\DependencyInjection\ModeraFileRepositoryExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class ModeraFileRepositoryExtensionTest extends \PHPUnit\Framework\TestCase
{
    public function testLoad(): void
    {
        $ext = new ModeraFileRepositoryExtension();

        $wannabeInterceptorsProviderDef = new Definition();

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setDefinition('foo-service', $wannabeInterceptorsProviderDef);

        $dummyConfig = [
            'modera_file_repository' => [
                'interceptors_provider' => 'foo-service',
            ],
        ];

        $ext->load($dummyConfig, $containerBuilder);

        $params = $containerBuilder->getParameterBag()->all();

        $this->assertArrayHasKey(ModeraFileRepositoryExtension::CONFIG_KEY, $params);

        // checking dynamic service linking
        $this->assertSame(
            $wannabeInterceptorsProviderDef,
            $containerBuilder->getDefinition('modera_file_repository.intercepting.interceptors_provider'),
        );
        $this->assertTrue(\count($params) > 1);
    }
}

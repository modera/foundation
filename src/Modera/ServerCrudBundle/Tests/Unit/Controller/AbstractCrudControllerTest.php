<?php

namespace Modera\ServerCrudBundle\Tests\Unit\Controller;

use Modera\ServerCrudBundle\Controller\AbstractCrudController;
use Modera\ServerCrudBundle\DataMapping\DataMapperInterface;
use Modera\ServerCrudBundle\DependencyInjection\ModeraServerCrudExtension;
use Modera\ServerCrudBundle\Exceptions\BadConfigException;
use Modera\ServerCrudBundle\Service\ConfiguredServiceManager;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class AbstractCrudControllerTest extends \PHPUnit\Framework\TestCase
{
    public function testGetDataMapperContainerParameter(): void
    {
        $config = ['data_mapper' => 'configDefinedMapper'];

        $container = \Phake::partialMock(ContainerBuilder::class);
        $container->setParameter(ModeraServerCrudExtension::CONFIG_KEY, $config);
        $container->compile();

        $dataMapper = \Phake::mock(DataMapperInterface::class);
        \Phake::when($container)->get('configDefinedMapper')->thenReturn($dataMapper);

        /** @var AbstractCrudController $controller */
        $controller = \Phake::partialMock(AbstractCrudController::class);
        $controller->setContainer($container);

        $configuredServiceManager = new ConfiguredServiceManager($container);
        $controller->setConfiguredServiceManager($configuredServiceManager);

        \Phake::when($controller)->getConfig()->thenReturn(
            ['entity' => 'testValue', 'hydration' => 'testValue']
        );

        $this->assertSame($dataMapper, \Phake::makeVisible($controller)->getDataMapper());
    }

    public function testGetDataMapperInConfigParameterServiceNotPresentInDIContainer(): void
    {
        $this->expectException(ServiceNotFoundException::class);

        $config = ['data_mapper' => 'configDefinedMapper'];

        $container = \Phake::partialMock(ContainerBuilder::class);
        $container->setParameter(ModeraServerCrudExtension::CONFIG_KEY, $config);
        $container->compile();

        $dataMapper = \Phake::mock(DataMapperInterface::class);
        \Phake::when($container)->get('configDefinedMapper')->thenReturn($dataMapper);

        /** @var AbstractCrudController $controller */
        $controller = \Phake::partialMock(AbstractCrudController::class);
        $controller->setContainer($container);

        $configuredServiceManager = new ConfiguredServiceManager($container);
        $controller->setConfiguredServiceManager($configuredServiceManager);

        \Phake::when($controller)->getConfig()->thenReturn(
            [
                'create_default_data_mapper' => function () use ($container) {
                    return $container->get('nonExistingService');
                },
                'entity' => 'testValue',
                'hydration' => 'testValue',
            ]
        );

        \Phake::makeVisible($controller)->getDataMapper();
    }

    public function testGetDataMapperInConfigParameterAllOk(): void
    {
        $config = ['data_mapper' => 'configDefinedMapper'];

        /** @var ContainerBuilder $container */
        $container = \Phake::partialMock(ContainerBuilder::class);
        $container->setParameter(ModeraServerCrudExtension::CONFIG_KEY, $config);
        $container->compile();

        $dataMapper = \Phake::mock(DataMapperInterface::class);
        \Phake::when($container)->get('configDefinedMapper')->thenReturn($dataMapper);
        \Phake::when($container)->has('existingService')->thenReturn(true);
        \Phake::when($container)->get('existingService')->thenReturn($dataMapper);

        /** @var AbstractCrudController $controller */
        $controller = \Phake::partialMock(AbstractCrudController::class);
        $controller->setContainer($container);

        $configuredServiceManager = new ConfiguredServiceManager($container);
        $controller->setConfiguredServiceManager($configuredServiceManager);

        \Phake::when($controller)->getConfig()->thenReturn(
            [
                'create_default_data_mapper' => function () use ($container) {
                    return $container->get('existingService');
                },
                'entity' => 'testValue',
                'hydration' => 'testValue',
            ]
        );

        $this->assertSame($dataMapper, \Phake::makeVisible($controller)->getDataMapper());
    }

    public function testGetConfiguredServiceNoConfigOption(): void
    {
        $this->expectException(BadConfigException::class);
        $this->expectExceptionMessage('An error occurred while getting a configuration property "nonExisingService". No such property exists in config.');

        $config = ['nonExistingService' => 'configDefinedMapper'];

        /** @var ContainerBuilder $container */
        $container = \Phake::partialMock(ContainerBuilder::class);
        $container->setParameter(ModeraServerCrudExtension::CONFIG_KEY, $config);
        $container->compile();

        /** @var AbstractCrudController $controller */
        $controller = \Phake::partialMock(AbstractCrudController::class);
        $controller->setContainer($container);

        $configuredServiceManager = new ConfiguredServiceManager($container);
        $controller->setConfiguredServiceManager($configuredServiceManager);

        \Phake::makeVisible($controller)->getConfiguredService('nonExisingService');
    }

    public function testGetConfiguredServiceNoContainerService(): void
    {
        $this->expectException(BadConfigException::class);
        $this->expectExceptionMessage('An error occurred while getting a service for configuration property "entity_validator" using DI service with ID "nonExistingServiceId" - You have requested a non-existent service "nonExistingServiceId"');

        $config = ['entity_validator' => 'nonExistingServiceId'];

        /** @var ContainerBuilder $container */
        $container = \Phake::partialMock(ContainerBuilder::class);
        $container->setParameter(ModeraServerCrudExtension::CONFIG_KEY, $config);
        $container->compile();

        /** @var AbstractCrudController $controller */
        $controller = \Phake::partialMock(AbstractCrudController::class);
        $controller->setContainer($container);

        $configuredServiceManager = new ConfiguredServiceManager($container);
        $controller->setConfiguredServiceManager($configuredServiceManager);

        \Phake::makeVisible($controller)->getConfiguredService('entity_validator');
    }
}

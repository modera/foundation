<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Modera\ServerCrudBundle\Contributions\ControllerActionInterceptorsProvider;
use Modera\ServerCrudBundle\DataMapping\DefaultDataMapper;
use Modera\ServerCrudBundle\DataMapping\EntityDataMapperService;
use Modera\ServerCrudBundle\DataMapping\MethodInvocation\MethodInvocationParametersProvider;
use Modera\ServerCrudBundle\DataMapping\MethodInvocation\MethodInvocationParametersProviderInterface;
use Modera\ServerCrudBundle\EntityFactory\DefaultEntityFactory;
use Modera\ServerCrudBundle\ExceptionHandling\BypassExceptionHandler;
use Modera\ServerCrudBundle\Hydration\HydrationService;
use Modera\ServerCrudBundle\Intercepting\InterceptorsManager;
use Modera\ServerCrudBundle\NewValuesFactory\DefaultNewValuesFactory;
use Modera\ServerCrudBundle\Persistence\DefaultModelManager;
use Modera\ServerCrudBundle\Persistence\DoctrineRegistryPersistenceHandler;
use Modera\ServerCrudBundle\QueryBuilder\ArrayQueryBuilder;
use Modera\ServerCrudBundle\QueryBuilder\ResolvingAssociatedModelSortingField\SortingFieldResolver;
use Modera\ServerCrudBundle\QueryBuilder\ResolvingAssociatedModelSortingField\SortingFieldResolverInterface;
use Modera\ServerCrudBundle\Service\ConfiguredServiceManager;
use Modera\ServerCrudBundle\Util\JavaBeansObjectFieldsManager;
use Modera\ServerCrudBundle\Validation\DefaultEntityValidator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->private()
            ->autowire()
            ->autoconfigure()
    ;

    $services->set(ControllerActionInterceptorsProvider::class);

    $services->set(DefaultDataMapper::class)->public();

    $services->set(EntityDataMapperService::class)
        ->arg('$complexFieldValueConvertersProvider', service('modera_server_crud.complex_field_value_converters_provider'))
    ;

    $services->set(MethodInvocationParametersProvider::class)
        ->arg('$container', service('service_container'))
    ;
    $services->alias(MethodInvocationParametersProviderInterface::class, MethodInvocationParametersProvider::class);

    $services->set(DefaultEntityFactory::class)->public();

    $services->set(BypassExceptionHandler::class)->public();

    $services->set(HydrationService::class)
        ->public()
        ->arg('$container', service('service_container'))
    ;

    $services->set(InterceptorsManager::class)
        ->public()
        ->arg('$interceptorsProvider', service('modera_server_crud.intercepting.cai_provider'))
    ;

    $services->set(DefaultNewValuesFactory::class)
        ->public()
        ->arg('$container', service('service_container'))
    ;

    $services->set(DefaultModelManager::class)->public();

    $services->set(DoctrineRegistryPersistenceHandler::class)->public();

    $services->set(ArrayQueryBuilder::class);

    $services->set(SortingFieldResolver::class);
    $services->alias(SortingFieldResolverInterface::class, SortingFieldResolver::class);

    $services->set(ConfiguredServiceManager::class)
        ->public()
        ->arg('$container', service('service_container'))
    ;

    $services->set(JavaBeansObjectFieldsManager::class);

    $services->set(DefaultEntityValidator::class)
        ->public()
        ->arg('$container', service('service_container'))
    ;
};

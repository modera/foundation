<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Modera\FileRepositoryBundle\Authoring\AuthoringInterceptor;
use Modera\FileRepositoryBundle\Contributions\RoutingResourcesProvider;
use Modera\FileRepositoryBundle\EventListener\ContainerInjectorListener;
use Modera\FileRepositoryBundle\Filesystem\FilesystemMap;
use Modera\FileRepositoryBundle\Filesystem\FilesystemMapInterface;
use Modera\FileRepositoryBundle\Intercepting\DefaultInterceptorsProvider;
use Modera\FileRepositoryBundle\Intercepting\MimeSaverInterceptor;
use Modera\FileRepositoryBundle\Repository\AsIsKeyGenerator;
use Modera\FileRepositoryBundle\Repository\FileRepository;
use Modera\FileRepositoryBundle\Repository\UniqidKeyGenerator;
use Modera\FileRepositoryBundle\ThumbnailsGenerator\CommandInterceptor;
use Modera\FileRepositoryBundle\ThumbnailsGenerator\Interceptor;
use Modera\FileRepositoryBundle\ThumbnailsGenerator\ThumbnailsGenerator;
use Modera\FileRepositoryBundle\UrlGeneration\UrlGenerator;
use Modera\FileRepositoryBundle\Validation\FilePropertiesValidationInterceptor;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->private()
            ->autowire()
            ->autoconfigure()
    ;

    $services->set(AuthoringInterceptor::class)->public();

    $services->set(RoutingResourcesProvider::class);

    $services->set(ContainerInjectorListener::class)
        ->arg('$container', service('service_container'))
    ;

    $services->set(FilesystemMap::class)
        ->arg('$filesystemMap', service('knp_gaufrette.filesystem_map'))
    ;
    $services->alias(FilesystemMapInterface::class, FilesystemMap::class)->public();

    $services->set(DefaultInterceptorsProvider::class)
        ->arg('$container', service('service_container'))
    ;

    $services->set(MimeSaverInterceptor::class)->public();

    $services->set(AsIsKeyGenerator::class)->public();
    // TODO: remove, BC
    $services->alias('modera_file_repository.repository.as_is_generator', AsIsKeyGenerator::class)->public();

    $services->set(FileRepository::class)
        ->public()
        ->arg('$container', service('service_container'))
    ;
    // TODO: remove, BC
    $services->alias('modera_file_repository.repository.file_repository', FileRepository::class)->public();

    // TODO: refactor
    $services->set('modera_file_repository.repository.uniqid_key_generator', UniqidKeyGenerator::class)->public();
    $services->set('modera_file_repository.repository.uniqid_key_generator_preserved_extension', UniqidKeyGenerator::class)
        ->public()
        ->arg('$preserveExtension', true)
    ;

    $services->set(Interceptor::class)->public();
    // TODO: remove, BC
    $services->alias('modera_file_repository.interceptors.thumbnails_generator.interceptor', Interceptor::class)->public();

    $services->set(CommandInterceptor::class)
        ->public()
        ->arg('$deploymentName', param('deployment.name'));
    // TODO: remove, BC
    $services->alias(CommandInterceptor::ID, CommandInterceptor::class)->public();

    $services->set(ThumbnailsGenerator::class);

    $services->set(UrlGenerator::class)
        ->public()
        ->arg('$routeName', param('modera_file_repository.config.controller.get_file_route'))
    ;
    // TODO: remove, BC
    $services->alias('modera_file_repository.stored_file.url_generator', UrlGenerator::class)->public();

    $services->set(FilePropertiesValidationInterceptor::class)->public();
    // TODO: remove, BC
    $services->alias('modera_file_repository.validation.file_properties_validation_interceptor', FilePropertiesValidationInterceptor::class)->public();
};

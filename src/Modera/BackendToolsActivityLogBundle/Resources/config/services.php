<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Modera\BackendToolsActivityLogBundle\AuthorResolving\ActivityAuthorResolver;
use Modera\BackendToolsActivityLogBundle\AutoSuggest\FilterAutoSuggestService;
use Modera\BackendToolsActivityLogBundle\Contributions\ToolsSectionsProvider;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->private()
            ->autowire()
            ->autoconfigure()
    ;

    $services->set(ActivityAuthorResolver::class);

    $services->set(FilterAutoSuggestService::class);

    $services->set(ToolsSectionsProvider::class);
};

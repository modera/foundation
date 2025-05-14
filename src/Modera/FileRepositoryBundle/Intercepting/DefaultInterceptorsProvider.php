<?php

namespace Modera\FileRepositoryBundle\Intercepting;

use Modera\FileRepositoryBundle\Authoring\AuthoringInterceptor;
use Modera\FileRepositoryBundle\Entity\Repository;
use Modera\FileRepositoryBundle\Validation\FilePropertiesValidationInterceptor;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * You should not use this class directly.
 *
 * This implementation of interceptors provider looks at config available (Repository::getConfig())
 * in repository and if it has a configuration key "interceptors" then all strings inside this array
 * will be treated as service container ids and corresponding services will be fetched from dependency
 * injection container which should be implementations of {@link OperationInterceptorInterface}.
 *
 * @internal
 *
 * @copyright 2015 Modera Foundation
 */
class DefaultInterceptorsProvider implements InterceptorsProviderInterface
{
    public function __construct(
        private readonly ContainerInterface $container,
    ) {
    }

    public function getInterceptors(Repository $repository): array
    {
        $interceptors = [];

        $ids = [
            FilePropertiesValidationInterceptor::class,
            MimeSaverInterceptor::class,
            AuthoringInterceptor::class,
        ];

        $config = $repository->getConfig();
        if (isset($config['interceptors']) && \is_array($config['interceptors'])) {
            /** @var string[] $ids */
            $ids = \array_merge($ids, $config['interceptors']);
        }

        foreach ($ids as $id) {
            /** @var OperationInterceptorInterface $interceptor */
            $interceptor = $this->container->get($id);
            $interceptors[] = $interceptor;
        }

        return $interceptors;
    }
}

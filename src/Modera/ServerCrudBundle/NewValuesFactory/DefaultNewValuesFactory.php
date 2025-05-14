<?php

namespace Modera\ServerCrudBundle\NewValuesFactory;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Will try to find a static method 'formatNewValues' on an entity resolved by using $config parameter
 * passed to getValues() method, if the method is found the following values will be passed:
 * $params, $config and instance of ContainerInterface. The static method must return a serializable data
 * structure that eventually will be sent bank to client-side.
 *
 * @copyright 2014 Modera Foundation
 */
class DefaultNewValuesFactory implements NewValuesFactoryInterface
{
    public function __construct(
        private readonly ContainerInterface $container,
    ) {
    }

    public function getValues(array $params, array $config): array
    {
        /** @var class-string $entityClass */
        $entityClass = $config['entity'];

        $methodName = 'formatNewValues';

        if (\method_exists($entityClass, $methodName)) {
            $refClass = new \ReflectionClass($entityClass);
            $refMethod = $refClass->getMethod($methodName);
            if ($refMethod->isStatic()) {
                $result = $refMethod->invokeArgs(null, [$params, $config, $this->container]);
                if (\is_array($result)) {
                    /** @var array<string, mixed> $arr */
                    $arr = $result;

                    return $arr;
                }
            }
        }

        return [];
    }
}

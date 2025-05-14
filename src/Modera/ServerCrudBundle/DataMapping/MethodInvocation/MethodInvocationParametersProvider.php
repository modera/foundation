<?php

namespace Modera\ServerCrudBundle\DataMapping\MethodInvocation;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @copyright 2024 Modera Foundation
 */
class MethodInvocationParametersProvider implements MethodInvocationParametersProviderInterface
{
    public function __construct(
        private readonly ContainerInterface $container,
    ) {
    }

    public function getParameters(string $fqcn, string $methodName): array
    {
        try {
            return $this->doGetParameters($fqcn, $methodName);
        } catch (\Exception $e) {
            throw new \RuntimeException("Unable to properly handle DataMapping\\MethodInvocation\\Params attribute on $fqcn::$methodName.", 0, $e);
        }
    }

    /**
     * @return array<?object>
     */
    protected function doGetParameters(string $fqcn, string $methodName): array
    {
        $reflectionMethod = new \ReflectionMethod($fqcn, $methodName);
        $attribute = $reflectionMethod->getAttributes(Params::class)[0] ?? null;
        /** @var ?Params $params */
        $params = $attribute?->newInstance();

        $result = [];
        foreach ($params->data ?? [] as $serviceName) {
            if ('*' === $serviceName[\strlen($serviceName) - 1]) { // optional service
                $result[] = $this->container->get($serviceName, ContainerInterface::NULL_ON_INVALID_REFERENCE);
            } else {
                $result[] = $this->container->get($serviceName, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE);
            }
        }

        return $result;
    }
}

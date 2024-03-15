<?php

namespace Modera\ServerCrudBundle\Hydration;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Service is responsible for converting given entity/entities to something that can be sent back to client-side.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2013 Modera Foundation
 */
class HydrationService
{
    private ContainerInterface $container;

    private PropertyAccessorInterface $accessor;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * @param callable|string[]|array<string, string>|mixed $hydrator
     *
     * @return array<mixed>
     */
    private function invokeHydrator($hydrator, object $object): array
    {
        if (\is_callable($hydrator)) {
            $result = $hydrator($object, $this->container);
            if (\is_array($result)) {
                return $result;
            }
        } elseif (\is_array($hydrator)) {
            $result = [];

            foreach ($hydrator as $key => $propertyPath) {
                $key = \is_numeric($key) ? $propertyPath : $key;

                try {
                    $result[$key] = $this->accessor->getValue($object, $propertyPath);
                } catch (\Exception $e) {
                    throw new \RuntimeException("Unable to resolve expression '$propertyPath' on ".\get_class($object), 0, $e);
                }
            }

            return $result;
        }

        throw new \RuntimeException('Invalid hydrator definition');
    }

    /**
     * @param array<mixed> $currentResult
     * @param array<mixed> $hydratorResult
     *
     * @return array<mixed>
     */
    private function mergeHydrationResult(array $currentResult, array $hydratorResult, HydrationProfile $profile, string $groupName): array
    {
        if ($profile->isGroupingNeeded()) {
            $currentResult[$groupName] = $hydratorResult;
        } else {
            $currentResult = \array_merge($currentResult, $hydratorResult);
        }

        return $currentResult;
    }

    /**
     * @param array<string, mixed> $config
     * @param ?string[]            $groups
     *
     * @return array<mixed>
     */
    public function hydrate(object $object, array $config, string $profileName, ?array $groups = null): array
    {
        $configAnalyzer = new ConfigAnalyzer($config);
        $profile = $configAnalyzer->getProfileDefinition($profileName);

        if (null === $groups) { // going to hydrate all groups if none are explicitly specified
            $result = [];

            foreach ($profile->getGroups() as $groupName) {
                $hydrator = $configAnalyzer->getGroupDefinition($groupName);

                $hydratorResult = $this->invokeHydrator($hydrator, $object);

                $result = $this->mergeHydrationResult($result, $hydratorResult, $profile, $groupName);
            }

            return $result;
        } else {
            $groupsToUse = \array_values($groups);

            // if there's only one group given then no grouping is going to be used
            if (1 === \count($groupsToUse)) {
                $hydrator = $configAnalyzer->getGroupDefinition($groupsToUse[0]);

                return $this->invokeHydrator($hydrator, $object);
            } else {
                $result = [];

                foreach ($groupsToUse as $groupName) {
                    $hydrator = $configAnalyzer->getGroupDefinition($groupName);

                    $hydratorResult = $this->invokeHydrator($hydrator, $object);

                    $result = $this->mergeHydrationResult($result, $hydratorResult, $profile, $groupName);
                }

                return $result;
            }
        }
    }
}

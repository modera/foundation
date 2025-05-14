<?php

namespace Modera\ServerCrudBundle\QueryBuilder\ResolvingAssociatedModelSortingField;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\Persistence\ManagerRegistry;
use Modera\ServerCrudBundle\Util\Toolkit;

/**
 * @copyright 2024 Modera Foundation
 */
class SortingFieldResolver implements SortingFieldResolverInterface
{
    public function __construct(
        private readonly ManagerRegistry $doctrineRegistry,
        private readonly string $defaultPropertyName = 'id',
    ) {
    }

    private function getDefaultPropertyName(string $entityFqcn): string
    {
        $names = [];
        foreach (Toolkit::getObjectProperties($entityFqcn) as $refProperty) {
            $names[] = $refProperty->getName();
        }
        if (!\in_array($this->defaultPropertyName, $names)) {
            throw new \RuntimeException("$entityFqcn::{$this->defaultPropertyName} doesn't exist.");
        }

        return $this->defaultPropertyName;
    }

    public function resolve(string $entityFqcn, string $fieldName): ?string
    {
        /** @var class-string $className */
        $className = $entityFqcn;

        $em = $this->doctrineRegistry->getManagerForClass($className);
        if (!$em) {
            throw new \RuntimeException(\sprintf('Manager for class "%s" not found', $entityFqcn));
        }

        /** @var ?ClassMetadataInfo $metadata */
        $metadata = $em->getClassMetadata($className);
        if (!$metadata) {
            throw new \RuntimeException("Unable to load metadata for class '$entityFqcn'.");
        }

        $fieldMapping = $metadata->getAssociationMapping($fieldName);

        $objectProperty = Toolkit::getObjectProperty($entityFqcn, $fieldName);
        if ($objectProperty) {
            $attribute = $objectProperty->getAttributes(QueryOrder::class)[0] ?? null;
            /** @var ?QueryOrder $queryOrder */
            $queryOrder = $attribute?->newInstance();
            if ($queryOrder) {
                return $queryOrder->name;
            }
        }

        $reflectionClass = new \ReflectionClass($fieldMapping['targetEntity']);
        $attribute = $reflectionClass->getAttributes(QueryOrder::class)[0] ?? null;
        /** @var ?QueryOrder $queryOrder */
        $queryOrder = $attribute?->newInstance();

        if ($queryOrder) {
            return $queryOrder->name;
        }

        return $this->getDefaultPropertyName($fieldMapping['targetEntity']);
    }
}

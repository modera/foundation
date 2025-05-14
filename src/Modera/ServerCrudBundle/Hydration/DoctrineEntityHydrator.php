<?php

namespace Modera\ServerCrudBundle\Hydration;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * @copyright 2014 Modera Foundation
 */
class DoctrineEntityHydrator
{
    private ?PropertyAccessorInterface $accessor = null;

    public static function create(
        EntityManagerInterface $entityManager,
    ): self {
        return new self($entityManager);
    }

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param array<string, string> $associativeFieldMappings
     * @param string[]              $excludeFields
     *
     * @return array<string, mixed>
     */
    public function hydrate(
        object $entity,
        array $associativeFieldMappings = [],
        array $excludeFields = [],
    ): array {
        if (!$this->accessor) {
            $this->accessor = PropertyAccess::createPropertyAccessor();
        }

        $meta = $this->entityManager->getClassMetadata(\get_class($entity));

        $result = [];
        foreach ($meta->getFieldNames() as $fieldName) {
            $result[$fieldName] = $this->accessor->getValue($entity, $fieldName);
        }

        foreach ($meta->getAssociationNames() as $fieldName) {
            if (isset($associativeFieldMappings[$fieldName])) {
                $expression = $associativeFieldMappings[$fieldName];

                $result[$fieldName] = $this->accessor->getValue($entity, $expression);
            } elseif (\method_exists($entity, '__toString')) {
                $result[$fieldName] = $entity->__toString();
            }
        }

        $finalResult = [];

        foreach ($result as $fieldName => $fieldValue) {
            if (\in_array($fieldName, $excludeFields)) {
                continue;
            }

            $finalResult[$fieldName] = $fieldValue;
        }

        return $finalResult;
    }
}

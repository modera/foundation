<?php

namespace Modera\ServerCrudBundle\DataMapping;

use Doctrine\ORM\EntityManagerInterface;

/**
 * @copyright 2013 Modera Foundation
 */
class DefaultDataMapper implements DataMapperInterface
{
    public function __construct(
        private readonly EntityDataMapperService $mapper,
        private readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @return string[]
     */
    protected function getAllowedFields(string $entityClass): array
    {
        /** @var class-string $entityClass */
        $metadata = $this->em->getClassMetadata($entityClass);

        $fields = $metadata->getFieldNames();
        foreach ($metadata->getAssociationMappings() as $mapping) {
            $fields[] = $mapping['fieldName'];
        }

        return $fields;
    }

    public function mapData(array $params, object $entity): void
    {
        $allowedFields = $this->getAllowedFields(\get_class($entity));

        $this->mapper->mapEntity($entity, $params, $allowedFields);
    }
}

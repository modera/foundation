<?php

namespace Modera\ServerCrudBundle\Persistence;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Modera\ServerCrudBundle\QueryBuilder\ArrayQueryBuilder;

/**
 * This implementation relies on ManagerRegistry, so it can support many EntityManagers for entities.
 *
 * @copyright 2016 Modera Foundation
 */
class DoctrineRegistryPersistenceHandler implements PersistenceHandlerInterface
{
    public function __construct(
        private readonly ManagerRegistry $doctrineRegistry,
        private readonly ArrayQueryBuilder $queryBuilder,
        private readonly bool $usePaginator = true,
    ) {
    }

    /**
     * @param array<string, mixed> $query
     */
    private function createPaginator(string $entityClass, array $query): Paginator
    {
        $qb = $this->queryBuilder->buildQueryBuilder($entityClass, $query);

        return new Paginator($qb->getQuery());
    }

    private function resolveEntityId(object $entity): int|string
    {
        // TODO: improve, resolve PK using entity's metadata - composite, non-surrogate PKs

        $entityClass = \get_class($entity);

        /** @var ClassMetadataInfo $meta */
        $meta = $this->getEntityManagerForClass($entityClass)->getClassMetadata($entityClass);
        $identifier = $meta->getSingleIdentifierFieldName();
        $method = 'get'.\ucfirst($identifier);

        if (!\in_array($method, \get_class_methods($entityClass))) {
            throw new \RuntimeException(\sprintf('Class %s must have method "%s()" (it is used to resolve PK).', $entityClass, $method));
        }

        $id = $entity->{$method}();

        if (null === $id) {
            throw new \RuntimeException('The entity must be stored and have an identifier.');
        }

        return $id;
    }

    private function getEntityManagerForClass(string|object $entityOrClass): EntityManagerInterface
    {
        /** @var class-string&string $entityClass */
        $entityClass = \is_object($entityOrClass) ? \get_class($entityOrClass) : $entityOrClass;

        $em = $this->doctrineRegistry->getManagerForClass($entityClass);
        if (!$em) {
            throw new \RuntimeException(\sprintf('Unable to resolve EntityManager for class "%s". Are you sure that the entity has been properly mapped ?', $entityClass));
        }

        if (!$em instanceof EntityManagerInterface) {
            // ExtjsQueryBuilder expects instances of EntityManagers, but the registry theoretically can also
            // return implementations of ObjectManager instead
            throw new \RuntimeException(\sprintf('Only implementations of %s are supported as managers, but class "%s" has been returned for entity "%s".', EntityManagerInterface::class, \get_class($em), $entityClass));
        }

        return $em;
    }

    public function resolveEntityPrimaryKeyFields(string $entityClass): array
    {
        $result = [];

        /** @var class-string $entityClass */
        $meta = $this->getEntityManagerForClass($entityClass)->getClassMetadata($entityClass);

        foreach ($meta->getFieldNames() as $fieldName) {
            $fieldMapping = $meta->getFieldMapping($fieldName);

            if (isset($fieldMapping['id']) && $fieldMapping['id']) {
                $result[] = $fieldName;
            }
        }

        return $result;
    }

    public function save(object $entity): OperationResult
    {
        $em = $this->getEntityManagerForClass($entity);

        $em->persist($entity);
        $em->flush();

        $result = new OperationResult();
        $result->reportEntity(
            \get_class($entity),
            $this->resolveEntityId($entity),
            OperationResult::TYPE_ENTITY_CREATED
        );

        return $result;
    }

    public function update(object $entity): OperationResult
    {
        $em = $this->getEntityManagerForClass($entity);

        $em->persist($entity);
        $em->flush();

        $result = new OperationResult();
        $result->reportEntity(
            \get_class($entity),
            $this->resolveEntityId($entity),
            OperationResult::TYPE_ENTITY_UPDATED
        );

        return $result;
    }

    public function updateBatch(array $entities): OperationResult
    {
        $result = new OperationResult();

        /** @var EntityManagerInterface[] $managersToFlush */
        $managersToFlush = [];

        // theoretically entities which are managed by different EMs can be given
        foreach ($entities as $entity) {
            $em = $this->getEntityManagerForClass($entity);

            // so here we are grouping EMs to later flush them all at once
            $managersToFlush[\spl_object_hash($em)] = $em;

            $em->persist($entity);

            $result->reportEntity(
                \get_class($entity),
                $this->resolveEntityId($entity),
                OperationResult::TYPE_ENTITY_UPDATED
            );
        }

        foreach ($managersToFlush as $em) {
            $em->flush();
        }

        return $result;
    }

    public function query(string $entityClass, array $query): array
    {
        if ($this->usePaginator) {
            /** @var \ArrayIterator $iterator */
            $iterator = $this->createPaginator($entityClass, $query)->getIterator();
            $result = $iterator->getArrayCopy();
        } else {
            $result = $this->queryBuilder->buildQuery($entityClass, $query)->getResult();
        }

        /** @var object[] $result */
        $result = $result;

        return $result;
    }

    public function getCount(string $entityClass, array $params): int
    {
        if ($this->usePaginator) {
            return $this->createPaginator($entityClass, $params)->count();
        }

        $qb = $this->queryBuilder->buildQueryBuilder($entityClass, $params);
        /** @var int $count */
        $count = $this->queryBuilder->buildCountQueryBuilder($qb)->getQuery()->getSingleScalarResult();

        return $count;
    }

    public function remove(array $entities): OperationResult
    {
        $result = new OperationResult();

        /** @var EntityManagerInterface[] $managersToFlush */
        $managersToFlush = [];

        // theoretically entities which are managed by different EMs can be given
        foreach ($entities as $entity) {
            $em = $this->getEntityManagerForClass($entity);
            $em->remove($entity);

            // so here we are grouping EMs to later flush them all at once
            $managersToFlush[\spl_object_hash($em)] = $em;

            $result->reportEntity(
                \get_class($entity),
                $this->resolveEntityId($entity),
                OperationResult::TYPE_ENTITY_REMOVED
            );
        }

        foreach ($managersToFlush as $em) {
            $em->flush();
        }

        return $result;
    }
}

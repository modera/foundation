<?php

namespace Modera\FoundationBundle\Exceptions;

/**
 * @deprecated
 *
 * Exception can be thrown when you expect to have entity returned when you queried database but nothing was really found
 *
 * @copyright 2013 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */
class EntityNotFoundException extends \RuntimeException
{
    /**
     * Fully qualified class name of exception.
     */
    private ?string $entityClass = null;

    /**
     * A query/criteria/dql/sql/you name it you used when tried to find the entity. For example:
     * - array('id' => 5)
     * - array('fistname' => 'John', 'lastname' => 'Doe')
     * - SELECT u FROM MyCompanyFooBundle:User u WHERE u.id = ?0.
     *
     * @var mixed Mixed value
     */
    private $query;

    /**
     * @var mixed[]
     */
    private array $queryParams = [];

    /**
     * @param mixed $query Mixed value
     */
    public function setQuery($query): void
    {
        $this->query = $query;
    }

    /**
     * @return mixed Mixed value
     */
    public function getQuery()
    {
        return $this->query;
    }

    public function setEntityClass(string $entityClass): void
    {
        $this->entityClass = $entityClass;
    }

    public function getEntityClass(): ?string
    {
        return $this->entityClass;
    }

    /**
     * @param mixed[] $queryParams
     */
    public function setQueryParams(array $queryParams): void
    {
        $this->queryParams = $queryParams;
    }

    /**
     * @return mixed[]
     */
    public function getQueryParams(): array
    {
        return $this->queryParams;
    }
}

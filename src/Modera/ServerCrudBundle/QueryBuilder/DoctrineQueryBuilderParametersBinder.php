<?php

namespace Modera\ServerCrudBundle\QueryBuilder;

use Doctrine\ORM\QueryBuilder;

/**
 * @internal
 *
 * @copyright 2024 Modera Foundation
 */
class DoctrineQueryBuilderParametersBinder
{
    private QueryBuilder $qb;

    /**
     * @var mixed[]
     */
    private array $values = [];

    public function __construct(QueryBuilder $qb)
    {
        $this->qb = $qb;
    }

    public function bind(mixed $value): int
    {
        $this->values[] = $value;

        return $this->getNextIndex();
    }

    public function getNextIndex(): int
    {
        return \count($this->values);
    }

    public function injectParameters(): void
    {
        $this->qb->setParameters($this->values);
    }
}

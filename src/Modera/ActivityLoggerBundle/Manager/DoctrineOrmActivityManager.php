<?php

namespace Modera\ActivityLoggerBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Modera\ActivityLoggerBundle\Entity\Activity;
use Modera\ServerCrudBundle\QueryBuilder\ArrayQueryBuilder;
use Psr\Log\AbstractLogger;

/**
 * This implementation uses Doctrine's ORM to store activities to database.
 *
 * @copyright 2014 Modera Foundation
 */
class DoctrineOrmActivityManager extends AbstractLogger implements ActivityManagerInterface
{
    public function __construct(
        private readonly EntityManagerInterface $om,
        private readonly ArrayQueryBuilder $queryBuilder,
    ) {
    }

    protected function createActivity(): Activity
    {
        return new Activity();
    }

    public function log(mixed $level, string|\Stringable $message, array $context = []): void
    {
        $activity = $this->createActivity();
        $activity->setMessage($message);

        if (\is_string($level)) {
            $activity->setLevel($level);
        }

        if (isset($context['author']) && (\is_string($context['author']) || \is_int($context['author']))) {
            $activity->setAuthor((string) $context['author']);
        }

        if (isset($context['type']) && \is_string($context['type'])) {
            $activity->setType($context['type']);
        }

        if (isset($context['meta']) && \is_array($context['meta'])) {
            /** @var array<string, mixed> $meta */
            $meta = $context['meta'];
            $activity->setMeta($meta);
        }

        $this->om->persist($activity);
        $this->om->flush($activity);
    }

    public function query(array $query): array
    {
        $qb = $this->queryBuilder->buildQueryBuilder(Activity::class, $query);

        /** @var int $total */
        $total = $this->queryBuilder->buildCountQueryBuilder($qb)->getQuery()->getSingleScalarResult();
        if ($total > 0) {
            /** @var Activity[] $items */
            $items = $qb->getQuery()->getResult();

            return [
                'items' => $items,
                'total' => $total,
            ];
        }

        return [
            'items' => [],
            'total' => 0,
        ];
    }
}

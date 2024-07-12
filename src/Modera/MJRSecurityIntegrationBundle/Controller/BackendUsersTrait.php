<?php

namespace Modera\MJRSecurityIntegrationBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Modera\MJRSecurityIntegrationBundle\ModeraMJRSecurityIntegrationBundle;
use Modera\SecurityBundle\Entity\User;
use Modera\SecurityBundle\Entity\UserInterface;
use Modera\SecurityBundle\Service\UserService;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2021 Modera Foundation
 */
trait BackendUsersTrait
{
    protected function createQueryBuilder(string $prefix = ''): QueryBuilder
    {
        /** @var EntityManagerInterface $em */
        $em = $this->container->get('doctrine.orm.entity_manager');

        /** @var UserService $userService */
        $userService = $this->container->get('modera_security.service.user_service');

        /** @var UserInterface $user */
        $user = $this->getUser();
        $rootUser = $userService->getRootUser();

        $qb = $em->createQueryBuilder();
        $qb
            ->from(User::class, $prefix.'u')
            ->leftJoin($prefix.'u.permissions', $prefix.'up')
            ->leftJoin($prefix.'u.groups', $prefix.'g')
            ->leftJoin($prefix.'g.permissions', $prefix.'gp')
            ->where(
                $qb->expr()->eq($prefix.'u.isActive', ':'.$prefix.'isActive')
            )
            ->andWhere(
                $qb->expr()->notIn($prefix.'u.id', [$user->getId(), $rootUser->getId()])
            )
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->in($prefix.'up.roleName', ':'.$prefix.'roleName'),
                    $qb->expr()->in($prefix.'gp.roleName', ':'.$prefix.'roleName')
                )
            )
        ;

        return $qb;
    }

    /**
     * @param array<string, mixed> $params
     */
    protected function createQuery(array $params): Query
    {
        $select = \implode(', ', [
            'partial u.{id, firstName, lastName, username}',
            'partial up.{id, name}',
            'partial g.{id, name}',
        ]);

        $qb = $this->createQueryBuilder()
            ->select(isset($params['select']) ? $params['select'] : $select)
            ->setParameter('isActive', true)
            ->setParameter('roleName', ModeraMJRSecurityIntegrationBundle::ROLE_BACKEND_USER)
        ;

        if (\is_array($params['filter'] ?? null)) {
            /** @var array{'property': string, 'value': string} $filter */
            foreach ($params['filter'] as $filter) {
                if ('name' === $filter['property']) {
                    $qb->andWhere(
                        $qb->expr()->orX(
                            $qb->expr()->like('u.username', ':name'),
                            $qb->expr()->like('u.firstName', ':name'),
                            $qb->expr()->like('u.lastName', ':name')
                        )
                    )->setParameter('name', '%'.$filter['value'].'%');
                }
            }
        }

        if (\is_array($params['sort'] ?? null)) {
            /** @var array{'property': string, 'direction': string} $sort */
            foreach ($params['sort'] as $sort) {
                $qb->orderBy('u.'.$sort['property'], $sort['direction']);
            }
        }

        /** @var int $start */
        $start = isset($params['start']) ? $params['start'] : 0;
        /** @var int $limit */
        $limit = isset($params['limit']) ? $params['limit'] : 25;
        $qb->setFirstResult($start)->setMaxResults($limit);

        return $qb->getQuery();
    }
}

<?php

namespace Modera\MJRSecurityIntegrationBundle\Controller;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Modera\MJRSecurityIntegrationBundle\ModeraMJRSecurityIntegrationBundle;
use Modera\SecurityBundle\Service\UserService;
use Modera\SecurityBundle\Entity\User;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2021 Modera Foundation
 */
trait BackendUsersTrait
{
    /**
     * @param string $prefix
     * @return QueryBuilder
     */
    protected function createQueryBuilder($prefix = '')
    {
        /* @var UserService $userService */
        $userService = $this->get('modera_security.service.user_service');

        $user = $this->getUser();
        $rootUser = $userService->getRootUser();

        $qb = $this->em()->createQueryBuilder();
        $qb
            ->from(User::class, $prefix . 'u')
            ->leftJoin($prefix . 'u.permissions', $prefix . 'up')
            ->leftJoin($prefix . 'u.groups', $prefix . 'g')
            ->leftJoin($prefix . 'g.permissions', $prefix . 'gp')
            ->where(
                $qb->expr()->eq($prefix . 'u.isActive', ':' . $prefix . 'isActive')
            )
            ->andWhere(
                $qb->expr()->notIn($prefix . 'u.id', [ $user->getId(), $rootUser->getId() ])
            )
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->in($prefix . 'up.roleName', ':' . $prefix . 'roleName'),
                    $qb->expr()->in($prefix . 'gp.roleName', ':' . $prefix . 'roleName')
                )
            )
        ;

        return $qb;
    }

    /**
     * @param array $params
     * @return Query
     */
    protected function createQuery(array $params)
    {
        $select = implode(', ', array(
            'partial u.{id, firstName, lastName, username}',
            'partial up.{id, name}',
            'partial g.{id, name}',
        ));

        $qb = $this->createQueryBuilder()
            ->select(isset($params['select']) ? $params['select'] : $select)
            ->setParameter('isActive', true)
            ->setParameter('roleName', ModeraMJRSecurityIntegrationBundle::ROLE_BACKEND_USER)
        ;

        if (isset($params['filter'])) {
            foreach ($params['filter'] as $filter) {
                if ('name' === $filter['property']) {
                    $qb->andWhere(
                        $qb->expr()->orX(
                            $qb->expr()->like('u.username', ':name'),
                            $qb->expr()->like('u.firstName', ':name'),
                            $qb->expr()->like('u.lastName', ':name')
                        )
                    )->setParameter('name', '%' . $filter['value'] . '%');
                }
            }
        }

        if (isset($params['sort'])) {
            foreach ($params['sort'] as $sort) {
                $qb->orderBy('u.' . $sort['property'], $sort['direction']);
            }
        }

        $start = isset($params['start']) ? $params['start'] : 0;
        $limit = isset($params['limit']) ? $params['limit'] : 25;
        $qb->setFirstResult($start)->setMaxResults($limit);

        return $qb->getQuery();
    }
}

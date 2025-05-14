<?php

namespace Modera\ServerCrudBundle\Tests\Functional\QueryBuilder;

use Modera\ServerCrudBundle\QueryBuilder\ArrayQueryBuilder;
use Modera\ServerCrudBundle\QueryBuilder\ResolvingAssociatedModelSortingField\MutableSortingFieldResolver;
use Modera\ServerCrudBundle\Tests\Functional\AbstractTestCase;
use Modera\ServerCrudBundle\Tests\Functional\DummyAddress;
use Modera\ServerCrudBundle\Tests\Functional\DummyOrder;
use Modera\ServerCrudBundle\Tests\Functional\DummyUser;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class ArrayQueryBuilderTest extends AbstractTestCase
{
    private ArrayQueryBuilder $builder;

    public static function doSetUpBeforeClass(): void
    {
        parent::doSetUpBeforeClass();
        self::createUsers();
    }

    public function doSetUp(): void
    {
        /** @var ArrayQueryBuilder $builder */
        $builder = self::getContainer()->get(ArrayQueryBuilder::class);
        $this->builder = $builder;

        /** @var TokenStorageInterface $ts */
        $ts = self::getContainer()->get('security.token_storage');

        $qb = $builder->buildQueryBuilder(DummyUser::class, [
            'filter' => [
                ['property' => 'id', 'value' => 'eq:1'],
            ],
        ]);

        /** @var DummyUser[] $users */
        $users = $qb->getQuery()->getResult();

        $token = new UsernamePasswordToken($users[0], 'main', ['ROLE_ADMIN']);
        $ts->setToken($token);
    }

    public function testBuildQueryBuilderEmptyFilter(): void
    {
        $qb = $this->builder->buildQueryBuilder(DummyUser::class, []);

        $users = $qb->getQuery()->getResult();
        $this->assertEquals(3, \count($users));
        $this->assertEquals(1, $users[0]->id);
        $this->assertEquals(2, $users[1]->id);
        $this->assertEquals(3, $users[2]->id);
    }

    public function testBuildQueryBuilderWithEqFilter(): void
    {
        $qb = $this->builder->buildQueryBuilder(DummyUser::class, [
            'filter' => [
                ['property' => 'id', 'value' => 'eq:1'],
            ],
        ]);

        $users = $qb->getQuery()->getResult();
        $this->assertEquals(1, \count($users));
        $this->assertEquals(1, $users[0]->id);
    }

    public function testBuildQueryBuilderWithInFilter(): void
    {
        $qb = $this->builder->buildQueryBuilder(DummyUser::class, [
            'filter' => [
                ['property' => 'id', 'value' => 'in:1,3'],
            ],
        ]);

        $users = $qb->getQuery()->getResult();
        $this->assertEquals(2, \count($users));
        $this->assertEquals(1, $users[0]->id);
        $this->assertEquals(3, $users[1]->id);
    }

    public function testBuildQueryBuilderWithEmptyInFilter(): void
    {
        $qb = $this->builder->buildQueryBuilder(DummyUser::class, [
            'filter' => [
                ['property' => 'id', 'value' => 'in:'],
            ],
        ]);

        $users = $qb->getQuery()->getResult();

        $this->assertEquals(3, \count($users));
    }

    public function testBuildQueryBuilderWithIsNotNullFilter(): void
    {
        $qb = $this->builder->buildQueryBuilder(DummyUser::class, [
            'filter' => [
                ['property' => 'address', 'value' => 'isNull'],
            ],
        ]);

        $users = $qb->getQuery()->getResult();
        $this->assertEquals(1, \count($users));
    }

    public function testBuildQueryBuilderWithSortByDescWhereIdNotIn2(): void
    {
        $qb = $this->builder->buildQueryBuilder(DummyUser::class, [
            'sort' => [
                ['property' => 'id', 'direction' => 'DESC'],
            ],
            'filter' => [
                ['property' => 'id', 'value' => 'notIn:2'],
            ],
        ]);

        $users = $qb->getQuery()->getResult();
        $this->assertEquals(2, \count($users));
        $this->assertEquals(3, $users[0]->id);
        $this->assertEquals(1, $users[1]->id);
    }

    public function testBuildQueryWithJoins(): void
    {
        $qb = $this->builder->buildQueryBuilder(DummyUser::class, [
            'filter' => [
                ['property' => 'address.country.name', 'value' => 'eq:A'],
            ],
        ]);

        /** @var DummyUser[] $users */
        $users = $qb->getQuery()->getResult();

        $this->assertEquals(1, \count($users));
    }

    public function testBuildQueryWithFetch(): void
    {
        $qb = $this->builder->buildQueryBuilder(DummyUser::class, [
            'fetch' => [
                'address.country',
            ],
        ]);

        // fetch for root, for address, and for address.country
        $this->assertEquals(3, \count($qb->getDQLPart('select')));

        /** @var DummyUser[] $users */
        $users = $qb->getQuery()->getResult();

        $this->assertInstanceof(DummyUser::class, $users[0]);
        $this->assertFalse($users[0]->address instanceof \Doctrine\Persistence\Proxy);
        $this->assertFalse($users[0]->address->country instanceof \Doctrine\Persistence\Proxy);
    }

    public function testBuildQueryBuilderWhereUserAddressZip(): void
    {
        $qb = $this->builder->buildQueryBuilder(DummyUser::class, [
            'filter' => [
                ['property' => 'lastname', 'value' => 'eq:doe'],
                ['property' => 'address.zip', 'value' => 'like:10%'],
            ],
        ]);

        $users = $qb->getQuery()->getResult();
        $this->assertTrue(\is_array($users));
        $this->assertEquals(1, \count($users));
        /** @var DummyUser $user */
        $user = $users[0];
        $this->assertEquals('doe', $user->lastname);
        $this->assertNotNull($user->address);
        $this->assertEquals('1010', $user->address->zip);
    }

    public function testBuildQueryBuilderWithSkipAssocFilter(): void
    {
        $qb = $this->builder->buildQueryBuilder(DummyUser::class, [
            'filter' => [
                ['property' => 'address', 'value' => 'eq:-'],
            ],
        ]);

        /** @var DummyUser[] $users */
        $users = $qb->getQuery()->getResult();
        $this->assertEquals(3, \count($users));
    }

    public function testBuildCountQueryBuilderFilterByAssociatedField(): void
    {
        $fetchQb = $this->builder->buildQueryBuilder(DummyUser::class, [
            'filter' => [
                ['property' => 'address.zip', 'value' => 'like:10%'],
            ],
        ]);

        $countQb = $this->builder->buildCountQueryBuilder($fetchQb);

        $this->assertEquals(1, $countQb->getQuery()->getSingleScalarResult());
    }

    public function testBuildCountQueryBuilderWithJoinFilterAndOrder(): void
    {
        $fetchQb = $this->builder->buildQueryBuilder(DummyUser::class, [
            'filter' => [
                ['property' => 'address.zip', 'value' => 'isNull'],
            ],
            'sort' => [ // it simply will be removed
                ['property' => 'address', 'direction' => 'DESC'],
            ],
        ]);

        $countQb = $this->builder->buildCountQueryBuilder($fetchQb);

        $this->assertEquals(1, $countQb->getQuery()->getSingleScalarResult());
    }

    public function testBuildQueryOrderByAssociatedEntity(): void
    {
        $qb = $this->builder->buildQueryBuilder(DummyUser::class, [
            'sort' => [
                ['property' => 'address', 'direction' => 'DESC'],
            ],
        ]);

        /** @var DummyUser[] $users */
        $users = $qb->getQuery()->getResult();
        $this->assertEquals(3, \count($users));

        $this->assertEquals('jane', $users[0]->firstname);
        $this->assertEquals('john', $users[1]->firstname);
        $this->assertEquals('vassily', $users[2]->firstname);
    }

    public function testBuildQueryOrderByAssociatedEntityWithProvidedSortingFieldResolver(): void
    {
        $sortingResolver = $this->createMock(
            '\Modera\ServerCrudBundle\QueryBuilder\ResolvingAssociatedModelSortingField\SortingFieldResolverInterface'
        );
        $sortingResolver->expects($this->atLeastOnce())
            ->method('resolve')
            ->with($this->equalTo(DummyUser::class), $this->equalTo('address'))
            ->will($this->returnValue('street'));

        $qb = $this->builder->buildQueryBuilder(DummyUser::class, [
            'sort' => [
                ['property' => 'address', 'direction' => 'ASC'],
            ],
        ], $sortingResolver);

        $orderBy = $qb->getDQLPart('orderBy');
        $this->assertEquals(1, \count($orderBy));
        $this->assertTrue(false !== \strpos($orderBy[0], 'street ASC'));

        /** @var DummyUser[] $users */
        $users = $qb->getQuery()->getResult();
        $this->assertEquals(3, \count($users));

        $this->assertEquals('vassily', $users[0]->firstname);
        $this->assertEquals('jane', $users[1]->firstname);
        $this->assertEquals('john', $users[2]->firstname);
    }

    public function testBuildQueryOrderByNestedAssociation(): void
    {
        $resolver = new MutableSortingFieldResolver();
        $resolver->add(DummyOrder::class, 'user', 'address');
        $resolver->add(DummyUser::class, 'address', 'country');
        $resolver->add(DummyAddress::class, 'country', 'name');

        $qb = $this->builder->buildQueryBuilder(DummyOrder::class, [
            'sort' => [
                ['property' => 'user', 'direction' => 'DESC'],
            ],
        ], $resolver);

        // TODO: re-check
        $this->assertEquals(4, \count($qb->getDQLPart('join'), \COUNT_RECURSIVE));

        /** @var DummyOrder[] $result */
        $result = $qb->getQuery()->getResult();

        $this->assertEquals(2, \count($result));
        $this->assertEquals('ORDER-2', $result[0]->number);
        $this->assertEquals('ORDER-1', $result[1]->number);
    }

    public function testBuildQueryWithMemberOfManyToMany(): void
    {
        $qb = $this->builder->buildQueryBuilder(DummyUser::class, [
            'filter' => [
                // when IN is used in conjunction with TO_MANY ( MANY_TO_MANY, ONE_TO_MANY ) relations
                // then it will be treated in special way and MEMBER OF query will be generated
                ['property' => 'groups', 'value' => 'in:1,20'],
            ],
        ]);

        /** @var DummyUser[] $users */
        $users = $qb->getQuery()->getResult();
        $this->assertEquals(1, \count($users));
        $this->assertEquals('john', $users[0]->firstname);
        $this->assertEquals('doe', $users[0]->lastname);
    }

    public function testBuildQueryWithNotMemberOfAndManyToMany(): void
    {
        $qb = $this->builder->buildQueryBuilder(DummyUser::class, [
            'filter' => [
                ['property' => 'groups', 'value' => 'notIn:1'],
            ],
        ]);

        /** @var DummyUser[] $users */
        $users = $qb->getQuery()->getResult();
        $this->assertEquals(2, \count($users));
    }

    public function testBuildQueryBuilderWithSeveralEqORedFilter(): void
    {
        $qb = $this->builder->buildQueryBuilder(DummyUser::class, [
            'filter' => [
                [
                    'property' => 'id',
                    'value' => ['eq:1', 'eq:3'], // 1 or 3
                ],
            ],
        ]);

        $users = $qb->getQuery()->getResult();
        $this->assertEquals(2, \count($users));
        $this->assertEquals(1, $users[0]->id);
        $this->assertEquals(3, $users[1]->id);
    }

    public function testBuilderQueryWithOrFilter(): void
    {
        $qb = $this->builder->buildQueryBuilder(DummyUser::class, [
            'filter' => [
                [
                    ['property' => 'firstname', 'value' => 'eq:john'],
                    ['property' => 'lastname', 'value' => 'like:pup%'],
                ],
            ],
        ]);

        /** @var DummyUser[] $users */
        $users = $qb->getQuery()->getResult();

        $this->assertEquals(2, \count($users));
        $this->assertEquals('john', $users[0]->firstname);
        $this->assertEquals('pupkin', $users[1]->lastname);
    }

    public function testBuilderQueryWithComplexFetch(): void
    {
        $qb = $this->builder->buildQueryBuilder(DummyUser::class, [
            'filter' => [
                [
                    ['property' => 'lastname', 'value' => 'eq:doe'],
                ],
            ],
            'fetch' => [
                'firstname',
                'lastname',
                'fullname' => [
                    'function' => 'CONCAT',
                    'args' => [
                        ':firstname',
                        [
                            'function' => 'CONCAT',
                            'args' => [' ', ':lastname'],
                        ],
                    ],
                ],
            ],
            'sort' => [
                ['property' => 'id', 'direction' => 'ASC'],
            ],
        ]);

        $users = $qb->getQuery()->getResult();

        $this->assertEquals(2, \count($users));
        $this->assertArrayHasKey('firstname', $users[0]);
        $this->assertArrayHasKey('lastname', $users[0]);
        $this->assertArrayHasKey('fullname', $users[0]);
        $this->assertEquals('john', $users[0]['firstname']);
        $this->assertEquals('doe', $users[0]['lastname']);
        $this->assertEquals('john doe', $users[0]['fullname']);
    }

    public function testBuildQueryWithGroupBy(): void
    {
        $qb = $this->builder->buildQueryBuilder(DummyUser::class, [
            'fetch' => [
                'total' => [
                    'function' => 'COUNT',
                    'args' => [':id'],
                ],
            ],
            'groupBy' => ['address.zip'],
            'fetchRoot' => false,
        ]);

        $this->assertEquals(1, \count($qb->getDQLPart('select')));

        /** @var DummyUser[] $users */
        $users = $qb->getQuery()->getResult();

        $this->assertEquals(3, \count($users));
    }

    public function testBuildQueryWithOrderByAggregateColumnResult(): void
    {
        $baseParams = [
            'fetchRoot' => false,
            'fetch' => [
                'how_many' => [
                    'function' => 'COUNT',
                    'args' => [':id'],
                ],
                'lastname',
            ],
            'groupBy' => [
                'lastname',
            ],
        ];

        $ascParams = \array_merge($baseParams, [
            'sort' => [
                [
                    'property' => 'how_many',
                    'direction' => 'ASC',
                ],
            ],
        ]);

        $descParams = \array_merge($baseParams, [
            'sort' => [
                [
                    'property' => 'how_many',
                    'direction' => 'DESC',
                ],
            ],
        ]);

        $ascResult = $this->builder->buildQueryBuilder(DummyUser::class, $ascParams)->getQuery()->getResult();

        $this->assertEquals(2, \count($ascResult));
        $this->assertEquals(1, $ascResult[0]['how_many']);
        $this->assertEquals(2, $ascResult[1]['how_many']);

        $descResult = $this->builder->buildQueryBuilder(DummyUser::class, $descParams)->getQuery()->getResult();

        $this->assertEquals(2, \count($descResult));
        $this->assertEquals(2, $descResult[0]['how_many']);
        $this->assertEquals(1, $descResult[1]['how_many']);
    }

    public function testQueryingByDateField(): void
    {
        $now = new \DateTime('now');

        $result = $this->builder->buildQuery(DummyUser::class, [
            'filter' => [
                [
                    'property' => 'updatedAt',
                    'value' => 'gte:'.$now->format('d.m.y H:i'),
                ],
            ],
        ])->getResult();

        $this->assertEquals(1, \count($result));
    }

    public function testMultipleJoinsInFilters(): void
    {
        $qb = $this->builder->buildQueryBuilder(DummyUser::class, [
            'filter' => [
                // ExpressionManager was always using a 'previous allocated alias'
                // instead of resolving what "entity" given expression really belongs
                ['property' => 'address.country.id', 'value' => 'eq:1'],
                ['property' => 'address.city.id', 'value' => 'eq:1'],
            ],
        ]);

        // before the fix Doctrine would throw an exception:
        // [Semantical Error] ... Class ...\DummyCountry has no association named city
        $users = $qb->getQuery()->getResult();

        $this->assertEquals(0, \count($users));
    }
}

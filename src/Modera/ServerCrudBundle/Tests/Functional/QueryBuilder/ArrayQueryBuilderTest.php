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
        $this->builder = $builder = self::getContainer()->get('modera_server_crud.array_query_builder');

        /** @var TokenStorageInterface $ts */
        $ts = self::getContainer()->get('security.token_storage');

        $qb = $builder->buildQueryBuilder(DummyUser::class, array(
            'filter' => array(
                array('property' => 'id', 'value' => 'eq:1')
            )
        ));

        /* @var DummyUser[] $users */
        $users = $qb->getQuery()->getResult();

        $token = new UsernamePasswordToken($users[0], null, 'main', ['ROLE_ADMIN']);
        $ts->setToken($token);
    }

    public function testBuildQueryBuilderEmptyFilter()
    {
        $qb = $this->builder->buildQueryBuilder(DummyUser::class, array());

        $users = $qb->getQuery()->getResult();
        $this->assertEquals(3, count($users));
        $this->assertEquals(1, $users[0]->id);
        $this->assertEquals(2, $users[1]->id);
        $this->assertEquals(3, $users[2]->id);
    }

    public function testBuildQueryBuilderWithEqFilter()
    {
        $qb = $this->builder->buildQueryBuilder(DummyUser::class, array(
            'filter' => array(
                array('property' => 'id', 'value' => 'eq:1')
            )
        ));

        $users = $qb->getQuery()->getResult();
        $this->assertEquals(1, count($users));
        $this->assertEquals(1, $users[0]->id);
    }

    public function testBuildQueryBuilderWithInFilter()
    {
        $qb = $this->builder->buildQueryBuilder(DummyUser::class, array(
            'filter' => array(
                array('property' => 'id', 'value' => 'in:1,3')
            )
        ));

        $users = $qb->getQuery()->getResult();
        $this->assertEquals(2, count($users));
        $this->assertEquals(1, $users[0]->id);
        $this->assertEquals(3, $users[1]->id);
    }

    public function testBuildQueryBuilderWithEmptyInFilter()
    {
        $qb = $this->builder->buildQueryBuilder(DummyUser::class, array(
            'filter' => array(
                array('property' => 'id', 'value' => 'in:')
            )
        ));

        $users = $qb->getQuery()->getResult();

        $this->assertEquals(3, count($users));
    }

    public function testBuildQueryBuilderWithIsNotNullFilter()
    {
        $qb = $this->builder->buildQueryBuilder(DummyUser::class, array(
            'filter' => array(
                array('property' => 'address', 'value' => 'isNull')
            )
        ));

        $users = $qb->getQuery()->getResult();
        $this->assertEquals(1, count($users));
    }

    public function testBuildQueryBuilderWithSortByDescWhereIdNotIn2()
    {
        $qb = $this->builder->buildQueryBuilder(DummyUser::class, array(
            'sort' => array(
                array('property' => 'id', 'direction' => 'DESC')
            ),
            'filter' => array(
                array('property' => 'id', 'value' => 'notIn:2')
            )
        ));

        $users = $qb->getQuery()->getResult();
        $this->assertEquals(2, count($users));
        $this->assertEquals(3, $users[0]->id);
        $this->assertEquals(1, $users[1]->id);
    }

    public function testBuildQueryWithJoins()
    {
        $qb = $this->builder->buildQueryBuilder(DummyUser::class, array(
            'filter' => array(
                array('property' => 'address.country.name', 'value' => 'eq:A')
            )
        ));

        /** @var DummyUser[] $users */
        $users = $qb->getQuery()->getResult();

        $this->assertEquals(1, count($users));
    }

    public function testBuildQueryWithFetch()
    {
        $qb = $this->builder->buildQueryBuilder(DummyUser::class, array(
            'fetch' => array(
                'address.country'
            )
        ));

        // fetch for root, for address, and for address.country
        $this->assertEquals(3, count($qb->getDQLPart('select')));

        /* @var DummyUser[] $users */
        $users = $qb->getQuery()->getResult();

        $this->assertInstanceof(DummyUser::class, $users[0]);
        $this->assertFalse($users[0]->address instanceof \Doctrine\Common\Proxy\Proxy);
        $this->assertFalse($users[0]->address->country instanceof \Doctrine\Common\Proxy\Proxy);
    }

    public function testBuildQueryBuilderWhereUserAddressZip()
    {
        $qb = $this->builder->buildQueryBuilder(DummyUser::class, array(
            'filter' => array(
                array('property' => 'lastname', 'value' => 'eq:doe'),
                array('property' => 'address.zip', 'value' => 'like:10%')
            )
        ));

        $users = $qb->getQuery()->getResult();
        $this->assertTrue(is_array($users));
        $this->assertEquals(1, count($users));
        /* @var DummyUser $user */
        $user = $users[0];
        $this->assertEquals('doe', $user->lastname);
        $this->assertNotNull($user->address);
        $this->assertEquals('1010', $user->address->zip);
    }

    public function testBuildQueryBuilderWithSkipAssocFilter()
    {
        $qb = $this->builder->buildQueryBuilder(DummyUser::class, array(
            'filter' => array(
                array('property' => 'address', 'value' => 'eq:-')
            )
        ));

        /* @var DummyUser[] $users */
        $users = $qb->getQuery()->getResult();
        $this->assertEquals(3, count($users));
    }

    public function testBuildCountQueryBuilderFilterByAssociatedField()
    {
        $fetchQb = $this->builder->buildQueryBuilder(DummyUser::class, array(
            'filter' => array(
                array('property' => 'address.zip', 'value' => 'like:10%')
            )
        ));

        $countQb = $this->builder->buildCountQueryBuilder($fetchQb);

        $this->assertEquals(1, $countQb->getQuery()->getSingleScalarResult());
    }

    public function testBuildCountQueryBuilderWithJoinFilterAndOrder()
    {
        $fetchQb = $this->builder->buildQueryBuilder(DummyUser::class, array(
            'filter' => array(
                array('property' => 'address.zip', 'value' => 'isNull')
            ),
            'sort' => array( // it simply will be removed
                array('property' => 'address', 'direction' => 'DESC')
            )
        ));

        $countQb = $this->builder->buildCountQueryBuilder($fetchQb);

        $this->assertEquals(1, $countQb->getQuery()->getSingleScalarResult());
    }

    public function testBuildQueryOrderByAssociatedEntity()
    {
        $qb = $this->builder->buildQueryBuilder(DummyUser::class, array(
            'sort' => array(
                array('property' => 'address', 'direction' => 'DESC')
            )
        ));

        /* @var DummyUser[] $users */
        $users = $qb->getQuery()->getResult();
        $this->assertEquals(3, count($users));

        $this->assertEquals('jane', $users[0]->firstname);
        $this->assertEquals('john', $users[1]->firstname);
        $this->assertEquals('vassily', $users[2]->firstname);
    }

    public function testBuildQueryOrderByAssociatedEntityWithProvidedSortingFieldResolver()
    {
        $sortingResolver = $this->createMock(
            '\Modera\ServerCrudBundle\QueryBuilder\ResolvingAssociatedModelSortingField\SortingFieldResolverInterface'
        );
        $sortingResolver->expects($this->atLeastOnce())
            ->method('resolve')
            ->with($this->equalTo(DummyUser::class), $this->equalTo('address'))
            ->will($this->returnValue('street'));

        $qb = $this->builder->buildQueryBuilder(DummyUser::class, array(
            'sort' => array(
                array('property' => 'address', 'direction' => 'ASC')
            )
        ), $sortingResolver);

        $orderBy = $qb->getDQLPart('orderBy');
        $this->assertEquals(1, count($orderBy));
        $this->assertTrue(strpos($orderBy[0], 'street ASC') !== false);

        /* @var DummyUser[] $users */
        $users = $qb->getQuery()->getResult();
        $this->assertEquals(3, count($users));

        $this->assertEquals('vassily', $users[0]->firstname);
        $this->assertEquals('jane', $users[1]->firstname);
        $this->assertEquals('john', $users[2]->firstname);
    }

    public function testBuildQueryOrderByNestedAssociation()
    {
        $resolver = new MutableSortingFieldResolver();
        $resolver->add(DummyOrder::class, 'user', 'address');
        $resolver->add(DummyUser::class, 'address', 'country');
        $resolver->add(DummyAddress::class, 'country', 'name');

        $qb = $this->builder->buildQueryBuilder(DummyOrder::class, array(
            'sort' => array(
                array('property' => 'user', 'direction' => 'DESC')
            )
        ), $resolver);

        $this->assertEquals(4, count($qb->getDQLPart('join'), \COUNT_RECURSIVE)); // there must be three joins

        /* @var DummyOrder[] $result */
        $result = $qb->getQuery()->getResult();

        $this->assertEquals(2, count($result));
        $this->assertEquals('ORDER-2', $result[0]->number);
        $this->assertEquals('ORDER-1', $result[1]->number);
    }

    public function testBuildQueryWithMemberOfManyToMany()
    {
        $qb = $this->builder->buildQueryBuilder(DummyUser::class, array(
            'filter' => array(
                // when IN is used in conjunction with TO_MANY ( MANY_TO_MANY, ONE_TO_MANY ) relations
                // then it will treated in special way and MEMBER OF query will be generated
                array('property' => 'groups', 'value' => 'in:1,20')
            )
        ));

        /* @var DummyUser[] $users */
        $users = $qb->getQuery()->getResult();
        $this->assertEquals(1, count($users));
        $this->assertEquals('john', $users[0]->firstname);
        $this->assertEquals('doe', $users[0]->lastname);
    }

    public function testBuildQueryWithNotMemberOfAndManyToMany()
    {
        $qb = $this->builder->buildQueryBuilder(DummyUser::class, array(
            'filter' => array(
                array('property' => 'groups', 'value' => 'notIn:1')
            )
        ));

        /* @var DummyUser[] $users */
        $users = $qb->getQuery()->getResult();
        $this->assertEquals(2, count($users));
    }

    public function testBuildQueryBuilderWithSeveralEqORedFilter()
    {
        $qb = $this->builder->buildQueryBuilder(DummyUser::class, array(
            'filter' => array(
                array(
                    'property' => 'id',
                    'value' => array(
                        'eq:1', 'eq:3' // 1 or 3
                    )
                )
            )
        ));

        $users = $qb->getQuery()->getResult();
        $this->assertEquals(2, count($users));
        $this->assertEquals(1, $users[0]->id);
        $this->assertEquals(3, $users[1]->id);
    }

    public function testBuilderQueryWithOrFilter()
    {
        $qb = $this->builder->buildQueryBuilder(DummyUser::class, array(
            'filter' => array(
                array(
                    array('property' => 'firstname', 'value' => 'eq:john'),
                    array('property' => 'lastname', 'value' => 'like:pup%')
                )
            )
        ));

        /* @var DummyUser[] $users */
        $users = $qb->getQuery()->getResult();

        $this->assertEquals(2, count($users));
        $this->assertEquals('john', $users[0]->firstname);
        $this->assertEquals('pupkin', $users[1]->lastname);
    }

    public function testBuilderQueryWithComplexFetch()
    {
        $qb = $this->builder->buildQueryBuilder(DummyUser::class, array(
            'filter' => array(
                array(
                    array('property' => 'lastname', 'value' => 'eq:doe'),
//                    array('property' => 'fullname', 'value' => 'like:jane%')
                )
            ),
            'fetch' => array(
                'firstname',
                'lastname',
                'fullname' => array(
                    'function' => 'CONCAT',
                    'args' => array(
                        ':firstname',
                        array(
                            'function' => 'CONCAT',
                            'args' => array(
                                ' ', ':lastname'
                            )
                        )
                    )
                )
            ),
            'sort' => array(
                array('property' => 'id', 'direction' => 'ASC')
            )
        ));

        $users = $qb->getQuery()->getResult();

        $this->assertEquals(2, count($users));
        $this->assertArrayHasKey('firstname', $users[0]);
        $this->assertArrayHasKey('lastname', $users[0]);
        $this->assertArrayHasKey('fullname', $users[0]);
        $this->assertEquals('john', $users[0]['firstname']);
        $this->assertEquals('doe', $users[0]['lastname']);
        $this->assertEquals('john doe', $users[0]['fullname']);
    }

    public function testBuildQueryWithGroupBy()
    {
        $qb = $this->builder->buildQueryBuilder(DummyUser::class, array(
            'fetch' => array(
                'total' => array(
                    'function' => 'COUNT',
                    'args' => array(
                        ':id'
                    )
                )
            ),
            'groupBy' => array(
                'address.zip'
            ),
            'fetchRoot' => false
        ));

        $this->assertEquals(1, count($qb->getDQLPart('select')));

        /* @var DummyUser[] $users */
        $users = $qb->getQuery()->getResult();

        $this->assertEquals(3, count($users));
    }

    public function testBuildQueryWithOrderByAggregateColumnResult()
    {
        $baseParams = array(
            'fetchRoot' => false,
            'fetch' => array(
                'how_many' => array(
                    'function' => 'COUNT',
                    'args' => array(':id')
                ),
                'lastname'
            ),
            'groupBy' => array(
                'lastname'
            )
        );

        $ascParams = array_merge($baseParams, array(
            'sort' => array(
                array(
                    'property' => 'how_many',
                    'direction' => 'ASC'
                )
            )
        ));

        $descParams = array_merge($baseParams, array(
            'sort' => array(
                array(
                    'property' => 'how_many',
                    'direction' => 'DESC'
                )
            )
        ));

        $ascResult = $this->builder->buildQueryBuilder(DummyUser::class, $ascParams)->getQuery()->getResult();

        $this->assertEquals(2, count($ascResult));
        $this->assertEquals(1, $ascResult[0]['how_many']);
        $this->assertEquals(2, $ascResult[1]['how_many']);

        $descResult = $this->builder->buildQueryBuilder(DummyUser::class, $descParams)->getQuery()->getResult();

        $this->assertEquals(2, count($descResult));
        $this->assertEquals(2, $descResult[0]['how_many']);
        $this->assertEquals(1, $descResult[1]['how_many']);
    }

    public function testQueryingByDateField()
    {
        $now = new \DateTime('now');

        $result = $this->builder->buildQuery(DummyUser::class, array(
            'filter' => array(
                array(
                    'property' => 'updatedAt',
                    'value' => 'gte:' . $now->format('d.m.y H:i')
                )
            )
        ))->getResult();

        $this->assertEquals(1, count($result));
    }

    public function testMultipleJoinsInFilters()
    {
        $qb = $this->builder->buildQueryBuilder(DummyUser::class, array(
            'filter' => array(
                // ExpressionManager was always using a 'previous allocated alias'
                // instead of resolving what "entity" given expression really belongs
                array('property' => 'address.country.id', 'value' => 'eq:1'),
                array('property' => 'address.city.id', 'value' => 'eq:1')
            )
        ));

        // before the fix Doctrine would throw an exception:
        // [Semantical Error] ... Class ...\DummyCountry has no association named city
        $users = $qb->getQuery()->getResult();

        $this->assertEquals(0, count($users));
    }
}

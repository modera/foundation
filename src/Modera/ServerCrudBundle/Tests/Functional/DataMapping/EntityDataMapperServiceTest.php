<?php

namespace Modera\ServerCrudBundle\Tests\Functional\DataMapping;

use Modera\ServerCrudBundle\DataMapping\EntityDataMapperService;
use Modera\ServerCrudBundle\QueryBuilder\ArrayQueryBuilder;
use Modera\ServerCrudBundle\Tests\Functional\AbstractTestCase;
use Modera\ServerCrudBundle\Tests\Functional\DummyUser;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class EntityDataMapperServiceTest extends AbstractTestCase
{
    private EntityDataMapperService $mapper;

    public static function doSetUpBeforeClass(): void
    {
        parent::doSetUpBeforeClass();
        self::createUsers();
    }

    public function doSetUp(): void
    {
        /** @var TokenStorageInterface $ts */
        $ts = self::getContainer()->get('security.token_storage');

        /** @var ArrayQueryBuilder $builder */
        $builder = self::getContainer()->get(ArrayQueryBuilder::class);

        $qb = $builder->buildQueryBuilder(DummyUser::class, [
            'filter' => [
                ['property' => 'id', 'value' => 'eq:1'],
            ],
        ]);

        /** @var DummyUser[] $users */
        $users = $qb->getQuery()->getResult();

        $token = new UsernamePasswordToken($users[0], 'main', ['ROLE_ADMIN']);
        $ts->setToken($token);

        $this->mapper = self::getContainer()->get(EntityDataMapperService::class);
    }

    public function testConvertDate(): void
    {
        $date = $this->mapper->convertDate('02.01.06');

        $this->assertInstanceOf('DateTime', $date);
        $this->assertEquals('02', $date->format('d'));
        $this->assertEquals('01', $date->format('m'));
        $this->assertEquals('06', $date->format('y'));
    }

    public function testConvertDateTime(): void
    {
        $date = $this->mapper->convertDateTime('02.01.06 15:04');

        $this->assertInstanceOf('DateTime', $date);
        $this->assertEquals('02', $date->format('d'));
        $this->assertEquals('01', $date->format('m'));
        $this->assertEquals('06', $date->format('y'));
        $this->assertEquals('15', $date->format('G'));
        $this->assertEquals('04', $date->format('i'));
    }

    public function testConvertBoolean(): void
    {
        $this->assertTrue($this->mapper->convertBoolean(1));
        $this->assertTrue($this->mapper->convertBoolean('1'));
        $this->assertTrue($this->mapper->convertBoolean('on'));
        $this->assertTrue($this->mapper->convertBoolean('true'));
        $this->assertTrue($this->mapper->convertBoolean(true));

        $this->assertFalse($this->mapper->convertBoolean(0));
        $this->assertFalse($this->mapper->convertBoolean('0'));
        $this->assertFalse($this->mapper->convertBoolean('off'));
        $this->assertFalse($this->mapper->convertBoolean('false'));
        $this->assertFalse($this->mapper->convertBoolean(false));
    }

    public function testMapEntity(): void
    {
        $user = new DummyUser();
        $userParams = [
            'email' => 'john.doe@example.org',
            'isActive' => 'off',
            'accessLevel' => '5',
            'meta' => [
                'foo' => 'bar',
            ],
        ];

        $this->mapper->mapEntity($user, $userParams, \array_keys($userParams));

        $this->assertEquals($userParams['email'], $user->email);
        $this->assertFalse($user->isActive);
        $this->assertEquals(5, $user->accessLevel);
        $this->assertEquals($userParams['meta'], $user->meta);
    }
}

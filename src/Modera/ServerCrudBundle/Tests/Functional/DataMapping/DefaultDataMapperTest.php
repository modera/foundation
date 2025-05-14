<?php

namespace Modera\ServerCrudBundle\Tests\Functional\DataMapping;

use Doctrine\ORM\Mapping as ORM;
use Modera\FoundationBundle\Testing\FunctionalTestCase;
use Modera\ServerCrudBundle\DataMapping\DefaultDataMapper;

#[ORM\Entity]
class DummyUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    public ?int $id = null;

    #[ORM\Column(type: 'string')]
    public string $firstname = '';

    #[ORM\Column(type: 'string')]
    public string $lastname = '';

    public function setFirstname(string $firstname): void
    {
        $this->firstname = $firstname;
    }

    public function setLastname(string $lastname): void
    {
        $this->lastname = $lastname;
    }
}

class DefaultDataMapperTest extends FunctionalTestCase
{
    public function testMapData()
    {
        /** @var DefaultDataMapper $mapper */
        $mapper = self::getContainer()->get(DefaultDataMapper::class);

        $this->assertInstanceOf(DefaultDataMapper::class, $mapper);

        $params = [
            'firstname' => 'Vassily',
            'lastname' => 'Pupkin',
        ];

        $user = new DummyUser();

        $mapper->mapData($params, $user);

        $this->assertEquals($params['firstname'], $user->firstname);
        $this->assertEquals($params['lastname'], $user->lastname);
    }
}

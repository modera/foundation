<?php

namespace Modera\ConfigBundle\Tests\Fixtures\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string')]
    private string $username;

    public function __construct(string $username)
    {
        $this->username = $username;
    }
}

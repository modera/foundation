<?php

namespace Modera\ServerCrudBundle\Tests\Functional;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Modera\ServerCrudBundle\DataMapping\PreferencesAwareUserInterface;
use Modera\ServerCrudBundle\QueryBuilder\ResolvingAssociatedModelSortingField\QueryOrder;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
class DummyUser implements UserInterface, PreferencesAwareUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    public ?int $id = null;

    #[ORM\Column(type: 'string')]
    public string $firstname = '';

    #[ORM\Column(type: 'string')]
    public string $lastname = '';

    #[ORM\Column(type: 'string', nullable: true)]
    public ?string $email = null;

    #[ORM\Column(type: 'boolean')]
    public bool $isActive = true;

    #[ORM\Column(type: 'integer')]
    public int $accessLevel = 0;

    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTimeInterface $updatedAt = null;

    #[ORM\OneToOne(targetEntity: DummyAddress::class, cascade: ['PERSIST'])]
    public ?DummyAddress $address = null;

    #[ORM\OneToMany(targetEntity: DummyNote::class, mappedBy: 'user')]
    public Collection $notes;

    #[ORM\ManyToOne(targetEntity: DummyCreditCard::class)]
    public ?DummyCreditCard $creditCard = null;

    #[ORM\ManyToMany(targetEntity: DummyGroup::class, inversedBy: 'users')]
    public Collection $groups;

    #[ORM\Column(type: 'integer', nullable: true)]
    public ?int $price = 0;

    #[ORM\Column(type: 'json')]
    public array $meta = [];

    public function __construct()
    {
        $this->notes = new ArrayCollection();
        $this->groups = new ArrayCollection();
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setFirstname(string $firstname): void
    {
        $this->firstname = $firstname;
    }

    public function setLastname(string $lastname): void
    {
        $this->lastname = $lastname;
    }

    public function addNote(DummyNote $note): void
    {
        if (!$this->notes->contains($note)) {
            $note->setUser($this);
            $this->notes[] = $note;
        }
    }

    public function setActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    public function setAccessLevel(int $accessLevel): void
    {
        $this->accessLevel = $accessLevel;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function setMeta(array $meta): void
    {
        $this->meta = $meta;
    }

    public function getPreferences(): array
    {
        return [
            PreferencesAwareUserInterface::SETTINGS_DATE_FORMAT => 'd.m.y',
            PreferencesAwareUserInterface::SETTINGS_DATETIME_FORMAT => 'd.m.y H:i',
        ];
    }

    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function getPassword(): string
    {
        return 'password';
    }

    public function getSalt(): string
    {
        return 'salt';
    }

    public function eraseCredentials(): void
    {
    }

    public function getUsername(): string
    {
        return \implode('-', [
            $this->id,
            $this->firstname,
            $this->lastname,
        ]);
    }

    public function getUserIdentifier(): string
    {
        return $this->getUsername();
    }

    public function __toString(): string
    {
        return $this->getUsername();
    }
}

#[ORM\Entity]
class DummyGroup
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    public ?int $id = null;

    #[ORM\Column(type: 'string')]
    public string $name = '';

    #[ORM\ManyToMany(targetEntity: DummyUser::class, mappedBy: 'groups')]
    public Collection $users;

    public function addUser(DummyUser $user): void
    {
        $user->groups->add($this);
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }
    }

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }
}

#[ORM\Entity]
class DummyCreditCard
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    public ?int $id = null;

    #[ORM\Column(type: 'integer')]
    public int $number = 0;
}

#[ORM\Entity]
#[QueryOrder('zip')]
class DummyAddress
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    public ?int $id = null;

    #[ORM\Column(type: 'string')]
    public string $zip = '';

    #[ORM\Column(type: 'string')]
    public string $street = '';

    #[ORM\ManyToOne(targetEntity: DummyCountry::class, cascade: ['PERSIST'])]
    public ?DummyCountry $country = null;

    #[ORM\ManyToOne(targetEntity: DummyCity::class, cascade: ['PERSIST'])]
    public ?DummyCity $city = null;
}

#[ORM\Entity]
class DummyCountry
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    public ?int $id = null;

    #[ORM\Column(type: 'string')]
    public string $name = '';

    #[ORM\OneToOne(targetEntity: DummyCity::class)]
    public ?DummyCity $capital = null;
}

#[ORM\Entity]
class DummyCity
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    public ?int $id = null;

    #[ORM\Column(type: 'string')]
    public string $name = '';
}

#[ORM\Entity]
class DummyNote
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    public ?int $id = null;

    #[ORM\Column(type: 'string')]
    public string $text = '';

    #[ORM\ManyToOne(targetEntity: DummyUser::class, inversedBy: 'notes')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private ?DummyUser $user = null;

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setText(string $text): void
    {
        $this->text = $text;
    }

    public function setUser(DummyUser $user): void
    {
        $this->user = $user;
    }
}

#[ORM\Entity]
class DummyOrder
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    public ?int $id = null;

    #[ORM\ManyToOne(targetEntity: DummyUser::class)]
    public ?DummyUser $user = null;

    #[ORM\Column(type: 'string')]
    public string $number = '';
}

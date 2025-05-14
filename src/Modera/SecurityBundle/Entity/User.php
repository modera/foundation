<?php

namespace Modera\SecurityBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Modera\SecurityBundle\PasswordStrength\BadPasswordException;
use Modera\SecurityBundle\PasswordStrength\PasswordManager;
use Modera\SecurityBundle\RootUserHandling\RootUserHandlerInterface;
use Modera\SecurityBundle\Validator\Constraints\Email;
use Modera\SecurityBundle\Validator\Constraints\Username;
use Modera\ServerCrudBundle\DataMapping\PreferencesAwareUserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @copyright 2014 Modera Foundation
 */
#[ORM\Entity]
#[ORM\Table(name: 'modera_security_user')]
#[UniqueEntity('personalId')]
#[UniqueEntity('username')]
#[UniqueEntity('email')]
class User implements UserInterface, PreferencesAwareUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'is_active', type: 'boolean')]
    private bool $isActive;

    #[Email]
    #[Assert\NotBlank]
    #[ORM\Column(type: 'string', length: 60, unique: true)]
    private string $email;

    #[Username]
    #[Assert\NotBlank]
    #[ORM\Column(type: 'string', length: 60, unique: true)]
    private string $username;

    #[ORM\Column(type: 'string', length: 64)]
    private string $password;

    #[ORM\Column(type: 'string', length: 32)]
    private string $salt;

    #[ORM\Column(name: 'personal_id', type: 'string', unique: true, nullable: true)]
    private ?string $personalId = null;

    #[ORM\Column(name: 'first_name', type: 'string', nullable: true)]
    private ?string $firstName = null;

    #[ORM\Column(name: 'last_name', type: 'string', nullable: true)]
    private ?string $lastName = null;

    #[ORM\Column(name: 'middle_name', type: 'string', nullable: true)]
    private ?string $middleName = null;

    #[ORM\Column(type: 'string', length: 1, nullable: true)]
    private ?string $gender = null;

    #[ORM\Column(type: 'integer')]
    protected int $state = self::STATE_NEW;

    #[ORM\Column(name: 'last_login', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $lastLogin = null;

    /**
     * @var Collection<int, Group>
     */
    #[ORM\ManyToMany(targetEntity: Group::class, inversedBy: 'users', cascade: ['persist'])]
    #[ORM\JoinTable(name: 'modera_security_users_groups')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'group_id', referencedColumnName: 'id')]
    private Collection $groups;

    /**
     * @var Collection<int, Permission>
     */
    #[ORM\ManyToMany(targetEntity: Permission::class, mappedBy: 'users', cascade: ['persist'])]
    private Collection $permissions;

    /**
     * You can use this field to keep meta-information associated with given user. To minimize chance of occurring
     * overlapped keys please store your values under bundle name which owns contributed configuration values. For
     * example, if you have a bundle AcmeFooBundle which wants to save some values to this field then store all values
     * under "acme_foo" key.
     *
     * @var array<string, mixed>
     */
    #[ORM\Column(type: 'json')]
    private array $meta = [];

    private ?RootUserHandlerInterface $rootUserHandler = null;

    public function __construct()
    {
        $this->isActive = true;
        $this->salt = \md5(\uniqid('', true));

        $this->groups = new ArrayCollection();
        $this->permissions = new ArrayCollection();
    }

    /**
     * Necessary for providing support for so called "root-users".
     *
     * @see #getRoles() method
     *
     * @private
     */
    public function init(?RootUserHandlerInterface $rootUserHandler): void
    {
        $this->rootUserHandler = $rootUserHandler;
    }

    public function addToGroup(Group $group): bool
    {
        if (!$group->hasUser($this)) {
            $group->addUser($this);

            return true;
        }

        return false;
    }

    public function addPermission(Permission $role): void
    {
        $role->addUser($this);
        if (!$this->permissions->contains($role)) {
            $this->permissions[] = $role;
        }
    }

    /**
     * @return Permission[]
     */
    public function getRawRoles(): array
    {
        $roles = [];
        foreach ($this->getGroups() as $group) {
            foreach ($group->getPermissions() as $role) {
                $roles[] = $role;
            }
        }
        foreach ($this->permissions as $role) {
            $roles[] = $role;
        }

        return $roles;
    }

    /**
     * This method also takes into account possibility that a user might be "root".
     *
     * @see #init() method
     * @see RootUserHandlerInterface
     */
    public function getRoles(): array
    {
        if ($this->rootUserHandler) {
            if ($this->rootUserHandler->isRootUser($this)) {
                return $this->rootUserHandler->getRoles();
            }
        }

        $roles = ['ROLE_USER'];
        foreach ($this->getRawRoles() as $role) {
            $roles[] = $role->getRoleName();
        }

        return $roles;
    }

    public function eraseCredentials(): void
    {
    }

    public function isEqualTo(SymfonyUserInterface $user): bool
    {
        if (!($user instanceof static)) {
            return false;
        }

        if ($this->username !== $user->getUsername()) {
            return false;
        }

        if ($this->password !== $user->getPassword()) {
            return false;
        }

        if ($this->salt !== $user->getSalt()) {
            return false;
        }

        if ($this->isActive !== $user->isActive()) {
            return false;
        }

        return true;
    }

    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'password' => $this->password,
            'salt' => $this->salt,
            'isActive' => $this->isActive,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function __unserialize(array $data): void
    {
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = \trim($email);
    }

    public function getUserIdentifier(): string
    {
        return $this->getUsername() ?? '';
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = \trim($username);
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @throws BadPasswordException
     */
    public function validateAndSetPassword(PasswordManager $passwordManager, string $plainPassword): void
    {
        $passwordManager->encodeAndSetPassword($this, $plainPassword);
    }

    /**
     * Most of the time you want to use #validateAndSetPassword() method instead.
     */
    public function setPassword(string $encodedPassword): void
    {
        $this->password = $encodedPassword;
    }

    public function getSalt(): ?string
    {
        return $this->salt;
    }

    public function setSalt(string $salt): void
    {
        $this->salt = $salt;
    }

    public function getPersonalId(): ?string
    {
        return $this->personalId;
    }

    public function setPersonalId(?string $personalId): void
    {
        $this->personalId = \preg_replace('/[^[:alnum:][:space:]-]/u', '', \trim($personalId ?? '')) ?: null;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): void
    {
        $this->firstName = \preg_replace('/[^[:alnum:][:space:]-]/u', '', \trim($firstName ?? '')) ?: null;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): void
    {
        $this->lastName = \preg_replace('/[^[:alnum:][:space:]-]/u', '', \trim($lastName ?? '')) ?: null;
    }

    public function getMiddleName(): ?string
    {
        return $this->middleName;
    }

    public function setMiddleName(?string $middleName): void
    {
        $this->middleName = \preg_replace('/[^[:alnum:][:space:]-]/u', '', \trim($middleName ?? '')) ?: null;
    }

    public function getFullName(string $pattern = 'first last'): ?string
    {
        $data = [
            'first' => $this->getFirstName() ?? '',
            'last' => $this->getLastName() ?? '',
            'middle' => $this->getMiddleName() ?? '',
        ];

        $keys = [];
        $values = [];
        foreach ($data as $key => $value) {
            $keys[] = '/\b'.$key.'\b/';
            $values[] = $value;
        }

        $name = \trim(\preg_replace($keys, $values, $pattern) ?? '');

        if (!$name) {
            return null;
        }

        return $name;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): void
    {
        $gender = \strtolower($gender ?? '');
        if (!\in_array($gender, [self::GENDER_MALE, self::GENDER_FEMALE])) {
            $gender = null;
        }

        $this->gender = $gender;
    }

    public function getState(): int
    {
        return $this->state;
    }

    public function setState(int $state): void
    {
        if (self::STATE_ACTIVE !== $state) {
            $state = self::STATE_NEW;
        }

        $this->state = $state;
    }

    public function getLastLogin(): ?\DateTimeInterface
    {
        return $this->lastLogin;
    }

    public function setLastLogin(\DateTimeInterface $lastLogin): void
    {
        $this->lastLogin = $lastLogin;
    }

    /**
     * @param Collection<int, Group> $groups
     */
    public function setGroups(Collection $groups): void
    {
        $this->groups = $groups;
    }

    /**
     * @return Collection<int, Group>
     */
    public function getGroups(): Collection
    {
        return $this->groups;
    }

    /**
     * @return Collection<int, Permission>
     */
    public function getPermissions(): Collection
    {
        return $this->permissions;
    }

    public function getMeta(): array
    {
        return $this->meta;
    }

    /**
     * @param array<string, mixed> $meta
     */
    public function setMeta(array $meta): void
    {
        $this->meta = $meta;
    }

    public function clearMeta(): void
    {
        $this->meta = [];
    }

    /**
     * @return array<string, string>
     */
    public function getPreferences(): array
    {
        return [
            PreferencesAwareUserInterface::SETTINGS_DATE_FORMAT => 'Y-m-d',
            PreferencesAwareUserInterface::SETTINGS_DATETIME_FORMAT => 'Y-m-d H:i:s',
        ];
    }
}

<?php

namespace Modera\ActivityLoggerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Modera\ActivityLoggerBundle\Model\ActivityInterface;

/**
 * This entity is not meant to be used directly, instead use
 * {@class Modera\ActivityLoggerBundle\Manager\DoctrineOrmActivityManager}. As a rule of thumb when working with
 * activities never rely on implementations but rather use {@class Modera\ActivityLoggerBundle\Model\ActivityInterface}
 * if you want to keep your code portable.
 *
 * @copyright 2014 Modera Foundation
 */
#[ORM\Entity]
#[ORM\Table(name: 'modera_activitylogger_activity')]
#[ORM\Index(name: 'author_idx', columns: ['author'])]
#[ORM\Index(name: 'type_idx', columns: ['type'])]
class Activity implements ActivityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $author = null;

    #[ORM\Column(type: 'string')]
    private string $type;

    #[ORM\Column(type: 'string')]
    private string $level;

    #[ORM\Column(type: 'string')]
    private string $message;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    /**
     * @var array<string, mixed>
     */
    #[ORM\Column(type: 'json')]
    private array $meta = [];

    public function __construct()
    {
        $this->setCreatedAt(new \DateTime('now'));
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setAuthor(?string $author): void
    {
        $this->author = $author;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setLevel(string $level): void
    {
        $this->level = $level;
    }

    public function getLevel(): string
    {
        return $this->level;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @param array<string, mixed> $meta
     */
    public function setMeta(array $meta): void
    {
        $this->meta = $meta;
    }

    /**
     * @return array<string, mixed>
     */
    public function getMeta(): array
    {
        return $this->meta;
    }
}

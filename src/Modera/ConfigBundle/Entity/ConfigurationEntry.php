<?php

namespace Modera\ConfigBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Modera\ConfigBundle\Config\ConfigurationEntryDefinition;
use Modera\ConfigBundle\Config\ConfigurationEntryInterface;
use Modera\ConfigBundle\Config\HandlerInterface;
use Modera\ConfigBundle\Config\ValueUpdatedHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Do not rely on methods exposed by this class outside this bundle, instead use methods declared by
 * {@class ConfigurationEntryInterface}.
 *
 * @copyright 2014 Modera Foundation
 */
#[ORM\Entity]
#[ORM\Table(name: 'modera_config_configurationproperty')]
#[ORM\Index(name: 'name_idx', columns: ['name'])]
#[ORM\HasLifecycleCallbacks]
class ConfigurationEntry implements ConfigurationEntryInterface
{
    public const TYPE_STRING = 0;
    public const TYPE_TEXT = 1;
    public const TYPE_INT = 2;
    public const TYPE_FLOAT = 3;
    public const TYPE_ARRAY = 4;
    public const TYPE_BOOL = 5;

    /**
     * @var string[]
     */
    private static array $fieldsMapping = [
        self::TYPE_INT => 'int',
        self::TYPE_STRING => 'string',
        self::TYPE_TEXT => 'text',
        self::TYPE_ARRAY => 'array',
        self::TYPE_FLOAT => 'float',
        self::TYPE_BOOL => 'bool',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * Technical name that you will use in your code to reference this configuration entry.
     */
    #[ORM\Column(type: 'string')]
    private string $name;

    /**
     * User understandable name for this configuration-entry.
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $readableName = null;

    /**
     * Optional name of category this configuration property should belong to.
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $category = null;

    /**
     * @var array<mixed>
     *
     * Optional configuration that will be used to configure implementation of
     * {@class \Modera\ConfigBundle\Config\HandlerInterface}.
     *
     * Available configuration properties:
     *
     *  * update_handler  -- DI service ID that implements {@class ValueUpdatedHandlerInterface} that must be invoked
     *                       when configuration entry is updated
     * * handler -- DI service ID of a class that implements {@class \Modera\ConfigBundle\Config\HandlerInterface}
     */
    #[ORM\Column(type: 'json')]
    private array $serverHandlerConfig = [];

    /**
     * @var array<mixed>
     *
     * Optional configuration that will be used on client-side ( frontend ) to configure editor for this configuration entry
     */
    #[ORM\Column(type: 'json')]
    private array $clientHandlerConfig = [];

    #[ORM\Column(type: 'string')]
    private string $savedAs = '';

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $stringValue = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $textValue = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $intValue = null;

    #[ORM\Column(type: 'decimal', precision: 20, scale: 4, nullable: true)]
    private ?string $floatValue = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $boolValue = null;

    /**
     * @var ?array<mixed>
     */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $arrayValue = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    private ?ContainerInterface $container = null;

    /**
     * Only those configuration properties will be shown in UI which have this property set to TRUE.
     */
    #[ORM\Column(type: 'boolean')]
    private bool $isExposed = true;

    /**
     * We won't allow to edit configuration properties whose isReadOnly field is set to FALSE.
     */
    #[ORM\Column(type: 'boolean')]
    private bool $isReadOnly = false;

    /**
     * Field is mapped dynamically if modera_config/owner_entity is defined.
     *
     * @see OwnerRelationMappingListener
     */
    private ?object $owner = null;

    public function __construct(string $name)
    {
        $this->setName($name);
    }

    public static function createFromDefinition(ConfigurationEntryDefinition $def): self
    {
        $me = new self($def->getName());
        $me->setValue($def->getValue());

        $me->applyDefinition($def);

        return $me;
    }

    public function applyDefinition(ConfigurationEntryDefinition $def): void
    {
        $this->setReadableName($def->getReadableName());
        $this->setServerHandlerConfig($def->getServerHandlerConfig());
        $this->setClientHandlerConfig($def->getClientHandlerConfig());
        $this->setExposed($def->isExposed());
        $this->setCategory($def->getCategory());
    }

    public function getContainer(): ?ContainerInterface
    {
        return $this->container;
    }

    public function setExposed(bool $isExposed): void
    {
        $this->isExposed = $isExposed;
    }

    public function isExposed(): bool
    {
        return $this->isExposed;
    }

    public function setReadOnly(bool $isReadOnly): void
    {
        $this->isReadOnly = $isReadOnly;
    }

    public function isReadOnly(): bool
    {
        return $this->isReadOnly;
    }

    public function init(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    /**
     * @private
     */
    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function updateUpdatedAt(): void
    {
        if (null !== $this->id) {
            $this->updatedAt = new \DateTime('now');
        }
    }

    #[ORM\PreUpdate]
    public function invokeUpdateHandler(): void
    {
        if ($this->getContainer() && \is_string($this->serverHandlerConfig['update_handler'] ?? null)) {
            /** @var ValueUpdatedHandlerInterface $updateHandler */
            $updateHandler = $this->getContainer()->get($this->serverHandlerConfig['update_handler']);
            $updateHandler->onUpdate($this);
        }
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function validate(): void
    {
        if (!\strlen($this->getSavedAs())) {
            throw new \DomainException(\sprintf('ConfigurationProperty "%s" is not fully configured ( did you set a value for it ? )', $this->getName()));
        }
    }

    private function hasServerHandler(): bool
    {
        return isset($this->serverHandlerConfig['handler']);
    }

    public function getHandler(): HandlerInterface
    {
        if (!$this->hasServerHandler()) {
            throw new \RuntimeException(\sprintf('Configuration-entry "%s" is not configured to use handlers, serverHandlerServiceId has not been specified!', $this->getName()));
        } elseif (null === $this->getContainer()) {
            throw new \RuntimeException(\sprintf('Configuration property "%s" is not initialized yet, use init() method.', $this->getName()));
        }

        if (!\is_string($this->serverHandlerConfig['handler'] ?? null)) {
            throw new \RuntimeException(\sprintf("Configuration property '%s' doesn't have handler configured!", $this->getName()));
        }

        $handlerServiceId = $this->serverHandlerConfig['handler'];

        $handler = $this->getContainer()->get($handlerServiceId);
        if (!($handler instanceof HandlerInterface)) {
            throw new \RuntimeException(\sprintf("Handler '%s' doesn't implement HandlerInterface! ( configuration-entry: %s )", $handlerServiceId, $this->getName()));
        }

        return $handler;
    }

    public function setDenormalizedValue(mixed $value): int
    {
        $this->{$this->getStorageFieldNameFromValue($value)} = $value;
        $this->savedAs = (string) $this->getFieldType($value);

        return (int) $this->savedAs;
    }

    public function getDenormalizedValue(): mixed
    {
        if (!isset(self::$fieldsMapping[$this->getSavedAs()])) {
            throw new \RuntimeException(\sprintf('Unable to resolve storage type "%s" for configuration-entry "%s"', $this->getSavedAs(), $this->getName()));
        }

        $fieldName = self::$fieldsMapping[$this->getSavedAs()].'Value';

        // doctrine hydrates decimal from database as strings
        // to avoid returning non-identical value that was initially
        // saved we will manually cast it to float
        $result = $this->$fieldName;
        if (self::TYPE_FLOAT === (int) $this->getSavedAs()) {
            $result = \floatval($result);
        }

        return $result;
    }

    public function setValue(mixed $value): mixed
    {
        $this->reset();

        if ($this->hasServerHandler()) {
            $value = $this->getHandler()->convertToStorageValue($value, $this);
        }

        return $this->setDenormalizedValue($value);
    }

    public function getValue(): mixed
    {
        if ($this->hasServerHandler()) {
            return $this->getHandler()->getValue($this);
        } else {
            return $this->getDenormalizedValue();
        }
    }

    public function getReadableValue(): mixed
    {
        if ($this->hasServerHandler()) {
            return $this->getHandler()->getReadableValue($this);
        } else {
            return $this->getDenormalizedValue();
        }
    }

    /**
     * Resets value of this configuration entry.
     */
    public function reset(): void
    {
        foreach (self::$fieldsMapping as $type => $name) {
            $this->{$name.'Value'} = null;
        }
    }

    /**
     * @param mixed $value Mixed value
     *
     * @throws \RuntimeException
     */
    public function getFieldType($value): int
    {
        if (\is_string($value)) {
            if (\mb_strlen($value) <= 254) {
                return self::TYPE_STRING;
            } else {
                return self::TYPE_TEXT;
            }
        } elseif (\is_float($value)) {
            return self::TYPE_FLOAT;
        } elseif (\is_int($value)) {
            return self::TYPE_INT;
        } elseif (\is_array($value)) {
            return self::TYPE_ARRAY;
        } elseif (\is_bool($value)) {
            return self::TYPE_BOOL;
        }

        throw new \RuntimeException(\sprintf('Unable to guess type of provided value! ( %s )', $this->getName()));
    }

    /**
     * @param mixed $value Mixed value
     */
    private function getStorageFieldNameFromValue($value): string
    {
        return self::$fieldsMapping[$this->getFieldType($value)].'Value';
    }

    // boilerplate:

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSavedAs(): string
    {
        return $this->savedAs;
    }

    /**
     * @param array<mixed> $serverHandlerConfig
     */
    public function setServerHandlerConfig(array $serverHandlerConfig): void
    {
        $this->serverHandlerConfig = $serverHandlerConfig;
    }

    /**
     * @return array<mixed>
     */
    public function getServerHandlerConfig(): array
    {
        return $this->serverHandlerConfig;
    }

    public function setReadableName(string $readableName): void
    {
        $this->readableName = $readableName;
    }

    public function getReadableName(): ?string
    {
        return $this->readableName;
    }

    /**
     * @param array<mixed> $clientConfiguratorConfig
     */
    public function setClientHandlerConfig(array $clientConfiguratorConfig): void
    {
        $this->clientHandlerConfig = $clientConfiguratorConfig;
    }

    /**
     * @return array<mixed>
     */
    public function getClientHandlerConfig(): array
    {
        return $this->clientHandlerConfig;
    }

    public function setCategory(string $category): void
    {
        $this->category = $category;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function getOwner(): ?object
    {
        return $this->owner;
    }

    public function setOwner(?object $owner): void
    {
        $this->owner = $owner;
    }
}

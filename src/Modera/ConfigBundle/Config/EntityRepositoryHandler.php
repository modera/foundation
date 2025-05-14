<?php

namespace Modera\ConfigBundle\Config;

use Doctrine\ORM\EntityManagerInterface;
use Modera\ConfigBundle\Entity\ConfigurationEntry;

/**
 * Allows to store/retrieve entities for {@class ConfigurationEntry}. In order this class to work,
 * instance of {@class ConfigurationEntry} must have two keys defined in its "serverHandlerConfig":
 * - entityFqcn : Fully qualified class name of an entity this handler will be working with
 * - toStringMethodName : A method name of the specified entity class that will be used
 *                        to get a string representation of it ( used in (#getReadableValue()) method )
 * - clientValueMethodName : Default value is 'getId', a method name to use to get a value
 *                           that will be stored in {@class ConfigurationEntry}.
 *
 * @copyright 2014 Modera Foundation
 */
class EntityRepositoryHandler implements HandlerInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @return class-string
     */
    private function getEntityFqcn(ConfigurationEntry $entry)
    {
        $cfg = $entry->getServerHandlerConfig();
        if (!\is_string($cfg['entityFqcn'] ?? null)) {
            throw MissingConfigurationParameterException::create($entry, 'entityFqcn');
        }

        /** @var class-string $className */
        $className = $cfg['entityFqcn'];

        return $className;
    }

    public function getReadableValue(ConfigurationEntry $entry): mixed
    {
        $cfg = $entry->getServerHandlerConfig();
        if (!isset($cfg['toStringMethodName'])) {
            throw MissingConfigurationParameterException::create($entry, 'toStringMethodName');
        }
        /** @var int|string $id */
        $id = $entry->getDenormalizedValue();
        $entity = $this->em->getRepository($this->getEntityFqcn($entry))->find($id);
        if (!$entity) {
            throw new \RuntimeException(\sprintf('Unable to find entity "%s" with id "%s"', $this->getEntityFqcn($entry), $id));
        }

        return $entity->{$cfg['toStringMethodName']}();
    }

    public function getValue(ConfigurationEntry $entry): mixed
    {
        return $this->em->getRepository($this->getEntityFqcn($entry))->find($entry->getDenormalizedValue());
    }

    public function convertToStorageValue(mixed $value, ConfigurationEntry $entry): mixed
    {
        /** @var object $value */
        if (!\is_a($value, $this->getEntityFqcn($entry))) {
            throw new \RuntimeException(\sprintf("Only instances of '%s' class can be persisted for configuration property '%s'.", $this->getEntityFqcn($entry), $entry->getName()));
        }

        $cfg = $entry->getServerHandlerConfig();
        $methodName = \is_string($cfg['clientValueMethodName'] ?? null) ? $cfg['clientValueMethodName'] : 'getId';
        if (!\in_array($methodName, \get_class_methods(\get_class($value)))) {
            throw new \RuntimeException(\sprintf("%s must have $methodName() method!", \get_class($value)));
        }

        return $value->$methodName();
    }
}

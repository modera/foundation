<?php

namespace Modera\ConfigBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Modera\ConfigBundle\Entity\ConfigurationEntry;

/**
 * Depending on semantic configuration checks if given configuration property is unique in general or unique
 * for a specific user.
 *
 * @internal
 *
 * @copyright 2016 Modera Foundation
 */
class UniquityValidator
{
    /**
     * @param array<mixed> $semanticConfig
     */
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly array $semanticConfig,
    ) {
    }

    public function isValidForSaving(ConfigurationEntry $entry): bool
    {
        $query = null;
        if ($this->semanticConfig['owner_entity'] && $entry->getOwner()) {
            $query = \sprintf('SELECT e.id FROM %s e WHERE e.name = ?0 AND e.owner = ?1', \get_class($entry));
            $query = $this->em->createQuery($query);

            $query->setParameters([$entry->getName(), $entry->getOwner()]);
        } else {
            $query = \sprintf('SELECT e.id FROM %s e WHERE e.name = ?0', \get_class($entry));
            $query = $this->em->createQuery($query);

            $query->setParameter(0, $entry->getName());
        }

        /** @var array<array{'id': int}> $result */
        $result = $query->getArrayResult();
        $isNameInUse = \count($result) > 0;

        if ($isNameInUse) {
            // if name is already in use then we will allow to save configuration property if it represents
            // the same records as in database
            return $entry->getId() == $result[0]['id'];
        }

        return true;
    }
}

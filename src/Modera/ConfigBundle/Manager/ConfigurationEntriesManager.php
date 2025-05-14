<?php

namespace Modera\ConfigBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Modera\ConfigBundle\Config\ConfigurationEntryInterface;
use Modera\ConfigBundle\Entity\ConfigurationEntry;

/**
 * @copyright 2016 Modera Foundation
 */
class ConfigurationEntriesManager implements ConfigurationEntriesManagerInterface
{
    /**
     * @param array<mixed> $semanticConfig
     */
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly array $semanticConfig = [],
        private readonly ?UniquityValidator $uniquityValidator = null,
    ) {
    }

    public function findOneByName(string $name, ?object $owner = null): ?ConfigurationEntryInterface
    {
        $qb = $this->em->createQueryBuilder();

        $qb
            ->select('e')
            ->from(ConfigurationEntry::class, 'e')
            ->andWhere(
                $qb->expr()->eq('e.name', '?1')
            )
            ->setMaxResults(1)
        ;

        $qb->setParameter(1, $name);

        if ($this->isOwnerConfigured()) {
            $qb->andWhere(
                $owner ? $qb->expr()->eq('e.owner', '?2') : $qb->expr()->isNull('e.owner')
            );

            if ($owner) {
                $qb->setParameter(2, $owner);
            }
        }

        /** @var ConfigurationEntryInterface[] $result */
        $result = $qb->getQuery()->getResult();

        return $result[0] ?? null;
    }

    private function isOwnerConfigured(): bool
    {
        return \is_string($this->semanticConfig['owner_entity'] ?? null);
    }

    public function findOneByNameOrDie(string $name, ?object $owner = null): ConfigurationEntryInterface
    {
        $result = $this->findOneByName($name, $owner);
        if (!$result) {
            throw new \RuntimeException(\sprintf('Unable to find required configuration property %s', $name));
        }

        return $result;
    }

    public function save(ConfigurationEntryInterface $entry): void
    {
        if (!($entry instanceof ConfigurationEntry)) {
            throw new \InvalidArgumentException('$entry must be an instance of '.ConfigurationEntry::class);
        }

        $this->em->beginTransaction();

        try {
            if ($this->uniquityValidator) {
                if (!$this->uniquityValidator->isValidForSaving($entry)) {
                    throw new ConfigurationEntryAlreadyExistsException(\sprintf('Configuration property with name "%s" already exists.', $entry->getName()));
                }
            }

            $this->em->persist($entry);
            $this->em->flush($entry);
        } catch (\Exception $e) {
            $this->em->rollback();

            throw $e;
        }

        $this->em->commit();
    }

    /**
     * @param object $owner
     *
     * @return ConfigurationEntryInterface[]
     */
    public function findAllExposed($owner = null): array
    {
        $qb = $this->em->createQueryBuilder();

        $qb
            ->select('e')
            ->from(ConfigurationEntry::class, 'e')
            ->andWhere(
                $qb->expr()->eq('e.isExposed', '?1')
            )
        ;

        $qb->setParameter(1, true);

        if ($this->isOwnerConfigured()) {
            $qb->andWhere(
                $owner ? $qb->expr()->eq('e.owner', '?2') : $qb->expr()->isNull('e.owner')
            );

            if ($owner) {
                $qb->setParameter(2, $owner);
            }
        }

        /** @var ConfigurationEntryInterface[] $arr */
        $arr = $qb->getQuery()->getResult();

        return $arr;
    }
}

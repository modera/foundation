<?php

namespace Modera\ConfigBundle\Listener;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\Persistence\Event\LoadClassMetadataEventArgs;
use Modera\ConfigBundle\Entity\ConfigurationEntry;

/**
 * @internal
 */
class OwnerRelationMappingListener
{
    /**
     * @param array{'owner_entity': string} $semanticConfig
     */
    public function __construct(
        private readonly array $semanticConfig,
    ) {
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $args): void
    {
        /** @var ClassMetadataInfo $mapping */
        $mapping = $args->getClassMetadata();

        if (ConfigurationEntry::class === $mapping->getName()) {
            $mapping->mapManyToOne([
                'fieldName' => 'owner',
                'type' => ClassMetadataInfo::MANY_TO_ONE,
                'isOwningSide' => true,
                'targetEntity' => $this->semanticConfig['owner_entity'],
            ]);
        }
    }
}

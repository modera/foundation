<?php

namespace Modera\ConfigBundle\Listener;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\Persistence\Event\LoadClassMetadataEventArgs;
use Modera\ConfigBundle\Entity\ConfigurationEntry;

/**
 * @internal
 *
 * @author Sergei Lissovski <sergei.lissovski@gmail.org>
 */
class OwnerRelationMappingListener
{
    /**
     * @var array{'owner_entity': string}
     */
    private array $semanticConfig;

    /**
     * @param array{'owner_entity': string} $semanticConfig
     */
    public function __construct(array $semanticConfig)
    {
        $this->semanticConfig = $semanticConfig;
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

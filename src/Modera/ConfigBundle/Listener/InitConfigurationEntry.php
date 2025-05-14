<?php

namespace Modera\ConfigBundle\Listener;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Modera\ConfigBundle\Entity\ConfigurationEntry;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Injects a reference to ConfigurationEntry entities when they are hydrated by Doctrine.
 *
 * @internal
 *
 * @copyright 2014 Modera Foundation
 */
class InitConfigurationEntry
{
    public function __construct(
        private readonly ContainerInterface $container,
    ) {
    }

    public function postLoad(ConfigurationEntry $entity, LifecycleEventArgs $args): void
    {
        $this->doInit($entity);
    }

    public function postPersist(ConfigurationEntry $entity, LifecycleEventArgs $args): void
    {
        $this->doInit($entity);
    }

    private function doInit(ConfigurationEntry $entity): void
    {
        $entity->init($this->container);
    }
}

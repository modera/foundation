<?php

namespace Modera\ConfigBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Modera\ConfigBundle\Entity\ConfigurationEntry;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Injects a reference to ConfigurationEntry entities when they are hydrated by Doctrine.
 *
 * @internal
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class InitConfigurationEntry
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function postLoad(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();
        if ($entity instanceof ConfigurationEntry) {
            $this->doInit($entity);
        }
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof ConfigurationEntry) {
            $this->doInit($entity);
        }
    }

    private function doInit(ConfigurationEntry $entity)
    {
        $entity->init($this->container);
    }
}

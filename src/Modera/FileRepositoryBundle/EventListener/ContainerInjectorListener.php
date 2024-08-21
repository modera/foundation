<?php

namespace Modera\FileRepositoryBundle\EventListener;

use Modera\FileRepositoryBundle\Entity\Repository;
use Modera\FileRepositoryBundle\Entity\StoredFile;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Injects a reference to service container to Repository entity whenever it is fetched
 * from database.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class ContainerInjectorListener
{
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function postLoad($entity, LifecycleEventArgs $event): void
    {
        $entity->init($this->container);
    }
}

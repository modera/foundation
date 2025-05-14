<?php

namespace Modera\SecurityBundle\EventListener;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Modera\SecurityBundle\Entity\User;
use Modera\SecurityBundle\RootUserHandling\RootUserHandlerInterface;

/**
 * Doctrine lifecycle listener which injects RootUserHandler instance when User entity is fetched from database.
 *
 * @private
 *
 * @copyright 2016 Modera Foundation
 */
class RootUserHandlerInjectionListener
{
    /**
     * We have to inject container instead of instance of RootUserHandler service
     * because of when the latter is injected it results in circular dependencies.
     */
    public function __construct(
        private readonly RootUserHandlerInterface $rootUserHandler,
    ) {
    }

    public function postLoad(User $entity, LifecycleEventArgs $event): void
    {
        $entity->init($this->rootUserHandler);
    }
}

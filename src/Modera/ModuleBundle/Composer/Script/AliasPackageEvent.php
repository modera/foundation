<?php

namespace Modera\ModuleBundle\Composer\Script;

use Composer\Installer\PackageEvent;
use Composer\Script\Event;

if (\class_exists(Event::class)) {
    class BaseEvent extends Event
    {
    }
} else {
    class BaseEvent
    {
        public function __construct()
        {
        }
    }
}

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2016 Modera Foundation
 */
class AliasPackageEvent extends BaseEvent
{
    protected PackageEvent $aliasOf;

    public function __construct(PackageEvent $event)
    {
        $this->aliasOf = $event;

        // @phpstan-ignore-next-line
        parent::__construct($event->getName(), $event->getComposer(), $event->getIO(), $event->isDevMode(), $event->getArguments(), $event->getFlags());
    }

    public function getAliasOf(): PackageEvent
    {
        return $this->aliasOf;
    }
}

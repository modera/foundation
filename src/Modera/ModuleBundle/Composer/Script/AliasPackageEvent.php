<?php

namespace Modera\ModuleBundle\Composer\Script;

use Composer\Script\Event;
use Composer\Installer\PackageEvent;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2016 Modera Foundation
 */
class AliasPackageEvent extends Event
{
    /**
     * @var PackageEvent
     */
    protected $aliasOf;

    /**
     * @param PackageEvent $event
     */
    public function __construct(PackageEvent $event)
    {
        $this->aliasOf = $event;

        parent::__construct(
            $event->getName(),
            $event->getComposer(),
            $event->getIO(),
            $event->isDevMode(),
            $event->getArguments(),
            $event->getFlags()
        );
    }

    /**
     * @return PackageEvent
     */
    public function getAliasOf()
    {
        return $this->aliasOf;
    }
}

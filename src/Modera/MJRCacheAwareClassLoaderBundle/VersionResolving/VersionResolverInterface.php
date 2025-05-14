<?php

namespace Modera\MJRCacheAwareClassLoaderBundle\VersionResolving;

/**
 * Implementations are responsible for resolving what version of MF is installed.
 *
 * @copyright 2014 Modera Foundation
 */
interface VersionResolverInterface
{
    /**
     * Method must return installed MF version.
     */
    public function resolve(): string;
}

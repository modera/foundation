<?php

namespace Modera\FileRepositoryBundle\Filesystem;

use Gaufrette\FilesystemInterface;

/**
 * @copyright 2024 Modera Foundation
 */
interface FilesystemMapInterface
{
    /**
     * Indicates whether there is a filesystem registered for the specified name.
     */
    public function has(string $name): bool;

    /**
     * Returns the filesystem registered for the specified name.
     *
     * @throw  \InvalidArgumentException when there is no filesystem registered for the specified name
     */
    public function get(string $name): FilesystemInterface;
}

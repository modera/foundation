<?php

namespace Modera\FileRepositoryBundle\Filesystem;

use Gaufrette\FilesystemInterface;
use Gaufrette\FilesystemMapInterface as GaufretteFilesystemMapInterface;

/**
 * @copyright 2024 Modera Foundation
 */
class FilesystemMap implements FilesystemMapInterface
{
    public function __construct(
        private readonly GaufretteFilesystemMapInterface $filesystemMap,
    ) {
    }

    public function has(string $name): bool
    {
        return $this->filesystemMap->has($name);
    }

    public function get(string $name): FilesystemInterface
    {
        return $this->filesystemMap->get($name);
    }
}

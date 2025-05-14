<?php

namespace Modera\FileRepositoryBundle\Repository;

/**
 * @copyright 2017 Modera Foundation
 */
class AsIsKeyGenerator implements StorageKeyGeneratorInterface
{
    public function generateStorageKey(\SplFileInfo $file, array $context = []): string
    {
        return $file->getFilename();
    }
}

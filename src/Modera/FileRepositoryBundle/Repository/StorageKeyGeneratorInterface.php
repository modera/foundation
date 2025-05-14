<?php

namespace Modera\FileRepositoryBundle\Repository;

/**
 * Implementations are responsible for generating keys which will be used to store files using Gaufrette filesystem adapters.
 *
 * @copyright 2014 Modera Foundation
 */
interface StorageKeyGeneratorInterface
{
    /**
     * @param array<mixed> $context
     */
    public function generateStorageKey(\SplFileInfo $file, array $context = []): string;
}

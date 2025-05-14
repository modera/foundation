<?php

namespace Modera\FileRepositoryBundle\Repository;

/**
 * @copyright 2014 Modera Foundation
 */
class UniqidKeyGenerator implements StorageKeyGeneratorInterface
{
    /**
     * @param bool $preserveExtension If this parameter is set to TRUE then when a filename is generated original's file
     *                                extension will be added to the new filename
     */
    public function __construct(
        private readonly bool $preserveExtension = false,
    ) {
    }

    public function generateStorageKey(\SplFileInfo $file, array $context = []): string
    {
        return \uniqid().($this->preserveExtension ? '.'.$file->getExtension() : '');
    }
}

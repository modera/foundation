<?php

namespace Modera\FileRepositoryBundle\Intercepting;

use Gaufrette\Adapter\AwsS3;
use Modera\FileRepositoryBundle\Entity\Repository;
use Modera\FileRepositoryBundle\Entity\StoredFile;

/**
 * Put content-type to s3 metadata.
 *
 * @author Stas Chychkan <stas.chichkan@modera.net>
 * @copyright 2020 Modera Foundation
 */
class MimeSaverInterceptor extends BaseOperationInterceptor
{
    public function onPut(StoredFile $storedFile, \SplFileInfo $file, Repository $repository, array $context = []): void
    {
        if ($storedFile->getMimeType()) {
            $adapter = $repository->getFileSystem()->getAdapter();
            if ($adapter instanceof AwsS3) {
                $adapter->setMetadata($storedFile->getStorageKey(), ['contentType' => $storedFile->getMimeType()]);
            }
        }
    }
}

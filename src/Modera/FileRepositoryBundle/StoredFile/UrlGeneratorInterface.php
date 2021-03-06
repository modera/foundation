<?php

namespace Modera\FileRepositoryBundle\StoredFile;

use Modera\FileRepositoryBundle\Entity\StoredFile;

/**
 * Implementations of this interface will be able to add custom url for stored file.
 *
 * @deprecated Use \Modera\FileRepositoryBundle\UrlGeneration\UrlGeneratorInterface instead
 *
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2015 Modera Foundation
 */
interface UrlGeneratorInterface
{
    /**
     * @param StoredFile $storedFile
     * @param $type
     *
     * @return string
     */
    public function generateUrl(StoredFile $storedFile, $type);
}

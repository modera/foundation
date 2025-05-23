<?php

namespace Modera\FileRepositoryBundle\UrlGeneration;

use Modera\FileRepositoryBundle\Entity\StoredFile;

/**
 * @copyright 2015 Modera Foundation
 */
interface UrlGeneratorInterface
{
    public function generateUrl(StoredFile $storedFile, int $type): string;
}

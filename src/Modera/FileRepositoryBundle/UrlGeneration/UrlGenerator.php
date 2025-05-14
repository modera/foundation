<?php

namespace Modera\FileRepositoryBundle\UrlGeneration;

use Modera\FileRepositoryBundle\Entity\StoredFile;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface as SymfonyUrlGeneratorInterface;

/**
 * @internal For time being still use StoredFile\UrlGenerator
 *
 * @copyright 2015 Modera Foundation
 */
class UrlGenerator implements UrlGeneratorInterface
{
    public function __construct(
        private readonly SymfonyUrlGeneratorInterface $urlGenerator,
        private readonly string $routeName,
    ) {
    }

    public function generateUrl(StoredFile $storedFile, int $type = SymfonyUrlGeneratorInterface::NETWORK_PATH): string
    {
        $storageKey = $storedFile->getStorageKey();
        $storageKey .= '/'.$storedFile->getRepository()->getName();
        $storageKey .= '/'.$storedFile->getFilename();

        return $this->urlGenerator->generate($this->routeName, [
            'storageKey' => $storageKey,
        ], $type);
    }
}

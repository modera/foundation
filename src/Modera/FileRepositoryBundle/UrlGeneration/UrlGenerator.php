<?php

namespace Modera\FileRepositoryBundle\UrlGeneration;

use Modera\FileRepositoryBundle\Entity\StoredFile;
use Symfony\Component\Routing\RouterInterface;

/**
 * @internal For time being still use StoredFile\UrlGenerator
 *
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2015 Modera Foundation
 */
class UrlGenerator implements UrlGeneratorInterface
{
    private RouterInterface $router;

    private string $routeName;

    public function __construct(RouterInterface $router, string $routeName)
    {
        $this->router = $router;
        $this->routeName = $routeName;
    }

    public function generateUrl(StoredFile $storedFile, int $type = RouterInterface::NETWORK_PATH): string
    {
        $storageKey = $storedFile->getStorageKey();
        $storageKey .= '/'.$storedFile->getRepository()->getName();
        $storageKey .= '/'.$storedFile->getFilename();

        return $this->router->generate($this->routeName, [
            'storageKey' => $storageKey,
        ], $type);
    }
}

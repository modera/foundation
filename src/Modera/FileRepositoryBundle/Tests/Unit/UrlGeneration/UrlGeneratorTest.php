<?php

namespace Modera\FileRepositoryBundle\Tests\Unit\UrlGeneration;

use Modera\FileRepositoryBundle\UrlGeneration\UrlGenerator;
use Symfony\Component\Routing\RouterInterface;

class UrlGeneratorTest extends \PHPUnit\Framework\TestCase
{
    public function testGenerateUrl(): void
    {
        $repository = \Phake::mock('Modera\FileRepositoryBundle\Entity\Repository');
        \Phake::when($repository)->getName()->thenReturn('repository-name');

        $storedFile = \Phake::mock('Modera\FileRepositoryBundle\Entity\StoredFile');
        \Phake::when($storedFile)->getStorageKey()->thenReturn('storage-key');
        \Phake::when($storedFile)->getRepository()->thenReturn($repository);
        \Phake::when($storedFile)->getFilename()->thenReturn('file-name');

        $routeName = 'some-route';
        $storageKey = $storedFile->getStorageKey();
        $storageKey .= '/'.$storedFile->getRepository()->getName();
        $storageKey .= '/'.$storedFile->getFilename();

        $url = $routeName.'/'.$storageKey;

        $router = \Phake::mock('Symfony\Component\Routing\RouterInterface');
        \Phake::when($router)->generate($routeName, [
            'storageKey' => $storageKey,
        ], RouterInterface::NETWORK_PATH)->thenReturn($url);

        $urlGenerator = new UrlGenerator($router, $routeName);

        $this->assertEquals($url, $urlGenerator->generateUrl($storedFile));
    }
}

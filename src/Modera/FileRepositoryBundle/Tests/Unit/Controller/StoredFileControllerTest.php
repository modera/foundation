<?php

namespace Modera\FileRepositoryBundle\Tests\Unit\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Modera\FileRepositoryBundle\Controller\StoredFileController;
use Modera\FileRepositoryBundle\DependencyInjection\ModeraFileRepositoryExtension;
use Modera\FileRepositoryBundle\Entity\StoredFile;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class DummyController extends StoredFileController
{
    public ?StoredFile $storedFile = null;

    protected function getFile(string $storageKey): ?StoredFile
    {
        return $this->storedFile;
    }

    public function setEnabled(bool $status): void
    {
        $containerBag = $this->container->get('parameter_bag');
        \Phake::when($containerBag)
            ->get(ModeraFileRepositoryExtension::CONFIG_KEY.'.controller.is_enabled')
            ->thenReturn($status)
        ;
    }
}

class StoredFileControllerTest extends \PHPUnit\Framework\TestCase
{
    private function createStoredFileController(): DummyController
    {
        $managerRegistry = \Phake::mock(ManagerRegistry::class);
        $user = \Phake::mock('Symfony\Component\Security\Core\User\UserInterface');
        $container = \Phake::mock('Symfony\Component\DependencyInjection\ContainerInterface');
        $token = \Phake::mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $tokenStorage = \Phake::mock('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface');
        $containerBag = \Phake::mock(ContainerBagInterface::class);

        \Phake::when($token)->getUser()->thenReturn($user);
        \Phake::when($tokenStorage)->getToken()->thenReturn($token);

        \Phake::when($container)->has('security.token_storage')->thenReturn(true);
        \Phake::when($container)->get('security.token_storage')->thenReturn($tokenStorage);

        \Phake::when($container)->has('parameter_bag')->thenReturn(true);
        \Phake::when($container)->get('parameter_bag')->thenReturn($containerBag);

        $ctrl = new DummyController($managerRegistry);
        $ctrl->setContainer($container);
        $ctrl->setEnabled(true);

        return $ctrl;
    }

    private function createStoredFile($storageKey, $content): ?StoredFile
    {
        if ($storageKey) {
            $parts = \explode('/', $storageKey);
            if (\count($parts) > 1) {
                $filename = $parts[\count($parts) - 1];
            } else {
                $filename = 'foo.txt';
            }
            list($name, $extension) = \explode('.', $filename);

            $mimeType = [
                'txt' => 'text/plain',
            ];

            $storedFile = \Phake::mock(StoredFile::class);

            \Phake::when($storedFile)->getStorageKey()->thenReturn($parts[0]);
            \Phake::when($storedFile)->getFilename()->thenReturn($filename);
            \Phake::when($storedFile)->getMimeType()->thenReturn($mimeType[$extension]);
            \Phake::when($storedFile)->getExtension()->thenReturn($extension);
            \Phake::when($storedFile)->getCreatedAt()->thenReturn(new \DateTime());
            \Phake::when($storedFile)->getContents()->thenReturn($content);
            \Phake::when($storedFile)->getSize()->thenReturn(\strlen($content));

            return $storedFile;
        }

        return null;
    }

    public function testGetAction(): void
    {
        $this->expectException(AccessDeniedException::class);

        $ctrl = $this->createStoredFileController();
        $request = \Phake::mock('Symfony\Component\HttpFoundation\Request');

        $resp = $ctrl->getAction($request, '');
        $this->assertEquals(Response::HTTP_NOT_FOUND, $resp->getStatusCode());
        $this->assertEquals('File not found.', $resp->getContent());

        $content = 'Hello World!';
        $storageKey = 'storage-key/repository-name/file-name.txt';
        $ctrl->storedFile = $this->createStoredFile($storageKey, $content);

        $resp = $ctrl->getAction($request, 'storage-key');
        $this->assertEquals(Response::HTTP_OK, $resp->getStatusCode());
        $this->assertEquals($content, $resp->getContent());
        $this->assertNull($resp->headers->get('content-disposition'));

        $resp = $ctrl->getAction($request, $storageKey);
        $this->assertEquals(Response::HTTP_OK, $resp->getStatusCode());
        $this->assertEquals($content, $resp->getContent());
        $this->assertNull($resp->headers->get('content-disposition'));

        $content = 'Download test';
        $ctrl->storedFile = $this->createStoredFile($storageKey, $content);
        \Phake::when($request)->get('dl')->thenReturn('');

        $resp = $ctrl->getAction($request, 'storage-key/repository-name/download-me.txt');
        $this->assertEquals(Response::HTTP_OK, $resp->getStatusCode());
        $this->assertEquals($content, $resp->getContent());
        $this->assertTrue(in_array($resp->headers->get('content-disposition'), [
            'attachment; filename=download-me.txt',
            'attachment; filename="download-me.txt"',
        ]));

        $resp = $ctrl->getAction($request, 'storage-key/foo.txt');
        $this->assertEquals(Response::HTTP_OK, $resp->getStatusCode());
        $this->assertEquals($content, $resp->getContent());
        $this->assertTrue(in_array($resp->headers->get('content-disposition'), [
            'attachment; filename=foo.txt',
            'attachment; filename="foo.txt"',
        ]));

        $ctrl->setEnabled(false);
        $resp = $ctrl->getAction($request, 'Exception');
    }
}

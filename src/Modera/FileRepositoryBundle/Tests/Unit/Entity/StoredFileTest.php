<?php

namespace Modera\FileRepositoryBundle\Tests\Unit\Entity;

use Modera\FileRepositoryBundle\DependencyInjection\ModeraFileRepositoryExtension;
use Modera\FileRepositoryBundle\Entity\Repository;
use Modera\FileRepositoryBundle\Entity\StoredFile;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\RouterInterface;

class StoredFileTest extends \PHPUnit\Framework\TestCase
{
    public function testConstruct(): void
    {
        $filename = \uniqid().'.txt';
        $filePath = \sys_get_temp_dir().\DIRECTORY_SEPARATOR.$filename;
        \file_put_contents($filePath, 'blah');

        $file = new File($filePath);

        $context = ['foo'];
        $dummyStorageKey = 'storage-key';

        $repository = $this->createMock('Modera\FileRepositoryBundle\Entity\Repository');
        $repository->expects($this->atLeastOnce())
                   ->method('generateStorageKey')
                   ->with($this->equalTo($file), $this->equalTo($context))
                   ->will($this->returnValue($dummyStorageKey));

        $storedFile = new StoredFile($repository, $file, $context);

        $this->assertEquals($filename, $storedFile->getFilename());
        $this->assertEquals($dummyStorageKey, $storedFile->getStorageKey());
        $this->assertEquals('txt', $storedFile->getExtension());
        $this->assertEquals('text/plain', $storedFile->getMimeType());
    }

    public function testConstructSettingAuthorAndOwnerFields(): void
    {
        $dummyStorageKey = 'storage-key';

        $file = new File(__FILE__);
        $context = [
            'author' => 'foo-author',
            'owner' => 'foo-owner',
        ];

        $repo = \Phake::mock(Repository::class);
        \Phake::when($repo)
            ->generateStorageKey($file, $context)
            ->thenReturn($dummyStorageKey)
        ;

        $storedFile = new StoredFile($repo, $file, $context);

        $this->assertEquals($context['author'], $storedFile->getAuthor());
        $this->assertEquals($context['owner'], $storedFile->getOwner());
    }

    public function testGetUrl(): void
    {
        $container = \Phake::mock(ContainerInterface::class);

        \Phake::when($container)
            ->getParameter(ModeraFileRepositoryExtension::CONFIG_KEY.'.default_url_generator')
            ->thenReturn('default_url_generator')
        ;

        \Phake::when($container)
            ->getParameter(ModeraFileRepositoryExtension::CONFIG_KEY.'.url_generators')
            ->thenReturn([
                'foo' => 'foo_url_generator',
                'bar' => 'bar_url_generator',
            ])
        ;

        $defaultUrlGenerator = \Phake::mock('Modera\FileRepositoryBundle\UrlGeneration\UrlGeneratorInterface');
        \Phake::when($container)->get('default_url_generator')->thenReturn($defaultUrlGenerator);

        $fooUrlGenerator = \Phake::mock('Modera\FileRepositoryBundle\UrlGeneration\UrlGeneratorInterface');
        \Phake::when($container)->get('foo_url_generator')->thenReturn($fooUrlGenerator);

        \Phake::when($container)->get('bar_url_generator')->thenReturn(new \stdClass());

        $context = [];
        $splFile = new \SplFileInfo(__FILE__);
        $repository = \Phake::mock('Modera\FileRepositoryBundle\Entity\Repository');
        \Phake::when($repository)->generateStorageKey($splFile, $context)->thenReturn('storage-key');
        $storedFile = new StoredFile($repository, $splFile, $context);
        $storedFile->init($container);

        \Phake::when($defaultUrlGenerator)->generateUrl($storedFile, RouterInterface::NETWORK_PATH)->thenReturn('default_url');
        \Phake::when($fooUrlGenerator)->generateUrl($storedFile, RouterInterface::NETWORK_PATH)->thenReturn('foo_url');

        \Phake::when($repository)->getConfig()->thenReturn(['filesystem' => 'foo']);
        $this->assertEquals('foo_url', $storedFile->getUrl());

        \Phake::when($repository)->getConfig()->thenReturn(['filesystem' => 'bar']);
        $this->assertEquals('default_url', $storedFile->getUrl());

        \Phake::when($repository)->getConfig()->thenReturn(['filesystem' => 'baz']);
        $this->assertEquals('default_url', $storedFile->getUrl());
    }
}

<?php

namespace Modera\FileUploaderBundle\Tests\Uploading;

use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\ExpanderBundle\Ext\ExtensionPoint;
use Modera\ExpanderBundle\Ext\ExtensionPointManager;
use Modera\ExpanderBundle\Ext\ExtensionProvider;
use Modera\FileUploaderBundle\Uploading\WebUploader;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

class WebUploaderTest extends \PHPUnit\Framework\TestCase
{
    public function testUpload(): void
    {
        $gateway = \Phake::mock('Modera\FileUploaderBundle\Uploading\UploadGatewayInterface');

        $provider = \Phake::mock(ContributorInterface::class);
        \Phake::when($provider)->getItems()->thenReturn([$gateway]);

        $request = \Phake::mock('Symfony\Component\HttpFoundation\Request');

        \Phake::when($gateway)->isResponsible($request)->thenReturn(true);
        \Phake::when($gateway)->upload($request)->thenReturn(new Response('foobar'));

        $container = \Phake::mock(ContainerInterface::class);
        \Phake::when($container)
            ->has('modera_file_uploader.uploading.gateways_provider')
            ->thenReturn(true)
        ;
        \Phake::when($container)
            ->get('modera_file_uploader.uploading.gateways_provider')
            ->thenReturn($provider)
        ;

        $extensionPointManager = \Phake::mock(ExtensionPointManager::class);
        \Phake::when($extensionPointManager)
            ->has('modera_file_uploader.uploading.gateways')
            ->thenReturn(true)
        ;
        \Phake::when($extensionPointManager)
            ->get('modera_file_uploader.uploading.gateways')
            ->thenReturn(new ExtensionPoint('modera_file_uploader.uploading.gateways'))
        ;

        $wu = new WebUploader(new ExtensionProvider($container, $extensionPointManager));

        $response = $wu->upload($request);

        \Phake::inOrder(
            \Phake::verify($provider)->getItems(),
            \Phake::verify($gateway)->isResponsible($request),
            \Phake::verify($gateway)->upload($request)
        );
        $this->assertEquals('foobar', $response->getContent());
    }
}

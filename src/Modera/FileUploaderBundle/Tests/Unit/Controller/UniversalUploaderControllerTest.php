<?php

namespace Modera\FileUploaderBundle\Tests\Unit\Controller;

use Modera\FileRepositoryBundle\Exceptions\FileValidationException;
use Modera\FileUploaderBundle\Controller\UniversalUploaderController;
use Modera\FileUploaderBundle\Uploading\WebUploader;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UniversalUploaderControllerTest extends \PHPUnit\Framework\TestCase
{
    private ContainerInterface $container;

    private UniversalUploaderController $ctr;

    private WebUploader $webUploader;

    protected function setUp(): void
    {
        $this->container = \Phake::mock(ContainerInterface::class);
        $this->webUploader = \Phake::mock(WebUploader::class);

        $this->ctr = new UniversalUploaderController($this->webUploader);
        $this->ctr->setContainer($this->container);

        $containerBag = \Phake::mock(ContainerBagInterface::class);
        \Phake::when($containerBag)
            ->get('modera_file_uploader.is_enabled')
            ->thenReturn(false)
        ;
        \Phake::when($this->container)->has('parameter_bag')->thenReturn(true);
        \Phake::when($this->container)->get('parameter_bag')->thenReturn($containerBag);
    }

    public function testUploadActionWhenNotEnabled(): void
    {
        $thrownException = null;
        try {
            $this->ctr->uploadAction(new Request());
        } catch (NotFoundHttpException $e) {
            $thrownException = $e;
        }

        $this->assertNotNull($thrownException);
        $this->assertEquals(404, $thrownException->getStatusCode());
    }

    private function teachContainer(
        Request $request,
        bool $isUploaderEnabled,
        \Exception|JsonResponse|null $uploaderResult = null,
    ): void {
        if ($uploaderResult instanceof \Exception) {
            \Phake::when($this->webUploader)
                ->upload($request)
                ->thenThrow($uploaderResult)
            ;
        } else {
            \Phake::when($this->webUploader)
                ->upload($request)
                ->thenReturn($uploaderResult)
            ;
        }
        $containerBag = $this->container->get('parameter_bag');
        \Phake::when($containerBag)
            ->get('modera_file_uploader.is_enabled')
            ->thenReturn($isUploaderEnabled)
        ;
    }

    public function testUploadActionWhenNoUploadHandledRequest(): void
    {
        $request = new Request();

        $this->teachContainer($request, true);

        $response = $this->ctr->uploadAction($request);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $response);

        $content = \json_decode($response->getContent(), true);
        $this->assertArrayHasKey('success', $content);
        $this->assertFalse($content['success']);
        $this->assertArrayHasKey('error', $content);
        $this->assertEquals('Unable to find an upload gateway that is able to process this file upload.', $content['error']);
    }

    public function testUploadActionSuccess(): void
    {
        $request = new Request();

        $result = [
            'success' => true,
            'blah' => 'foo',
        ];

        $this->teachContainer($request, true, new JsonResponse($result));

        $response = $this->ctr->uploadAction($request);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $response);

        $this->assertSame($result, json_decode($response->getContent(), true));
    }

    public function testUploadActionWithValidationException(): void
    {
        $request = new Request();

        $exception = FileValidationException::create(new \SplFileInfo(__FILE__), ['some error']);

        $this->teachContainer($request, true, $exception);

        $response = $this->ctr->uploadAction($request);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $response);

        $content = \json_decode($response->getContent(), true);
        $this->assertArrayHasKey('success', $content);
        $this->assertFalse($content['success']);
        $this->assertArrayHasKey('error', $content);
        $this->assertArrayHasKey('errors', $content);
        $this->assertTrue(is_array($content['errors']));
        $this->assertEquals(1, \count($content['errors']));
        $this->assertEquals('some error', $content['errors'][0]);
    }
}

<?php

namespace Modera\MJRCacheAwareClassLoaderBundle\Tests\Unit\EventListener;

use Modera\MJRCacheAwareClassLoaderBundle\EventListener\VersionInjectorEventListener;
use Modera\MJRCacheAwareClassLoaderBundle\VersionResolving\VersionResolverInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class VersionInjectorEventListenerTest extends \PHPUnit\Framework\TestCase
{
    private VersionResolverInterface $versionResolver;

    private Response $response;

    private Request $mockRequest;

    private ResponseEvent $mockEvent;

    public function setUp(): void
    {
        $this->versionResolver = \Phake::mock(VersionResolverInterface::class);
        \Phake::when($this->versionResolver)
            ->resolve()
            ->thenReturn('resolved-foo-version')
        ;

        $this->response = new Response();

        $this->mockRequest = \Phake::mock(Request::class);

        $kernel = \Phake::mock(HttpKernelInterface::class);

        $this->mockEvent = new ResponseEvent($kernel, $this->mockRequest, HttpKernelInterface::MAIN_REQUEST, $this->response);
    }

    public function testOnKernelResponseHappyPath(): void
    {
        \Phake::when($this->mockRequest)
            ->getPathInfo()
            ->thenReturn('/backend/direct')
        ;

        $listener = new VersionInjectorEventListener($this->versionResolver, [
            'listener_response_paths' => [
                'backend.*',
            ],
        ]);

        $listener->onKernelResponse($this->mockEvent);

        $headers = $this->response->headers->all();

        $headerName = strtolower(VersionInjectorEventListener::HEADER_NAME);
        $this->assertArrayHasKey($headerName, $headers);
        $this->assertTrue(isset($headers[$headerName][0]));
        $this->assertEquals('resolved-foo-version', $headers[$headerName][0]);
    }

    public function testOnKernelResponsePathDoesNotMatch(): void
    {
        \Phake::when($this->mockRequest)
            ->getPathInfo()
            ->thenReturn('/products/sections')
        ;

        $listener = new VersionInjectorEventListener($this->versionResolver, [
            'listener_response_paths' => [
                'backend.*',
            ],
        ]);

        $listener->onKernelResponse($this->mockEvent);

        $headers = $this->response->headers->all();

        $headerName = strtolower(VersionInjectorEventListener::HEADER_NAME);
        $this->assertArrayNotHasKey($headerName, $headers);
    }
}

<?php

namespace Modera\MJRCacheAwareClassLoaderBundle\Tests\Unit\EventListener;

use Modera\MJRCacheAwareClassLoaderBundle\EventListener\VersionInjectorEventListener;
use Modera\MJRCacheAwareClassLoaderBundle\VersionResolving\VersionResolverInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
class VersionInjectorEventListenerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var VersionResolverInterface
     */
    private $versionResolver;

    /**
     * @var Response
     */
    private $response;

    private $mockRequest;

    private $mockEvent;

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

        $this->mockEvent = new ResponseEvent($kernel, $this->mockRequest, HttpKernelInterface::MASTER_REQUEST, $this->response);
    }

    public function testOnKernelResponse_happyPath()
    {
        \Phake::when($this->mockRequest)
            ->getPathInfo()
            ->thenReturn('/backend/direct')
        ;

        $listener = new VersionInjectorEventListener($this->versionResolver, array(
            'listener_response_paths' => array(
                'backend.*',
            ),
        ));

        $listener->onKernelResponse($this->mockEvent);

        $headers = $this->response->headers->all();

        $headerName = strtolower(VersionInjectorEventListener::HEADER_NAME);
        $this->assertArrayHasKey($headerName, $headers);
        $this->assertTrue(isset($headers[$headerName][0]));
        $this->assertEquals('resolved-foo-version', $headers[$headerName][0]);
    }

    public function testOnKernelResponse_pathDoesNotMatch()
    {
        \Phake::when($this->mockRequest)
            ->getPathInfo()
            ->thenReturn('/products/sections')
        ;

        $listener = new VersionInjectorEventListener($this->versionResolver, array(
            'listener_response_paths' => array(
                'backend.*',
            ),
        ));

        $listener->onKernelResponse($this->mockEvent);

        $headers = $this->response->headers->all();

        $headerName = strtolower(VersionInjectorEventListener::HEADER_NAME);
        $this->assertArrayNotHasKey($headerName, $headers);
    }
}

<?php

namespace Modera\MJRSecurityIntegrationBundle\Tests\Unit\EventListener;

use Modera\MJRSecurityIntegrationBundle\EventListener\AjaxAuthenticationValidatingListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\ExceptionInterface;

class AjaxAuthenticationValidatingListenerTest extends \PHPUnit\Framework\TestCase
{
    private function createEvent(bool $isAjax, string $pathInfo = '', ?ExceptionInterface $e = null): ExceptionEvent
    {
        $request = \Phake::mock('Symfony\Component\HttpFoundation\Request');
        \Phake::when($request)->isXmlHttpRequest()->thenReturn($isAjax);
        \Phake::when($request)->getPathInfo()->thenReturn($pathInfo);

        $kernel = \Phake::mock(HttpKernelInterface::class);

        $event = new ExceptionEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $e ?: new \RuntimeException());

        return $event;
    }

    public function testOnKernelExceptionWithNotAjaxRequest(): void
    {
        $event = $this->createEvent(false);

        $lnr = new AjaxAuthenticationValidatingListener('/mega-backend');

        $this->assertEquals(AjaxAuthenticationValidatingListener::RESULT_NOT_AJAX, $lnr->kernelExceptionHandler($event));
    }

    public function testOnKernelExceptionWithNoBackend(): void
    {
        $event = $this->createEvent(true, '/another-backend');

        $lnr = new AjaxAuthenticationValidatingListener('/mega-backend');

        $this->assertEquals(AjaxAuthenticationValidatingListener::RESULT_NOT_BACKEND_REQUEST, $lnr->kernelExceptionHandler($event));
    }

    public function testOnKernelExceptionWithInvalidException(): void
    {
        $event = $this->createEvent(true, '/mega-backend');

        $lnr = new AjaxAuthenticationValidatingListener('/mega-backend');
        $lnr->kernelExceptionHandler($event);

        $this->assertNull($event->getResponse());
    }

    public function testOnKernelException(): void
    {
        $event = $this->createEvent(true, '/mega-backend', new AccessDeniedException());

        $lnr = new AjaxAuthenticationValidatingListener('/mega-backend');
        $lnr->kernelExceptionHandler($event);

        /** @var JsonResponse $response */
        $response = $event->getResponse();

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $response);
        $content = json_decode($response->getContent(), true);
        $this->assertTrue(\is_array($content));
        $this->assertArrayHasKey('success', $content);
        $this->assertFalse($content['success']);
        $this->assertArrayHasKey('message', $content);
        $this->assertTrue('' != $content['message']);
    }
}

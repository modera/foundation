<?php

namespace Modera\MJRSecurityIntegrationBundle\Tests\Unit\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Modera\MJRSecurityIntegrationBundle\EventListener\AjaxAuthenticationValidatingListener;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2015 Modera Foundation
 */
class AjaxAuthenticationValidatingListenerTest extends \PHPUnit\Framework\TestCase
{
    private function createEvent($isAjax, $pathInfo = '', $e = null)
    {
        $request = \Phake::mock('Symfony\Component\HttpFoundation\Request');
        \Phake::when($request)->isXmlHttpRequest()->thenReturn($isAjax);
        \Phake::when($request)->getPathInfo()->thenReturn($pathInfo);

        $kernel = \Phake::mock(HttpKernelInterface::class);

        $event = new ExceptionEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST, $e ?: new \RuntimeException());

        return $event;
    }

    public function testOnKernelExceptionWithNotAjaxRequest()
    {
        $event = $this->createEvent(false);

        $lnr = new AjaxAuthenticationValidatingListener('/mega-backend');

        $this->assertEquals(AjaxAuthenticationValidatingListener::RESULT_NOT_AJAX, $lnr->onKernelException($event));
    }

    public function testOnKernelExceptionWithNoBackend()
    {
        $event = $this->createEvent(true, '/another-backend');

        $lnr = new AjaxAuthenticationValidatingListener('/mega-backend');

        $this->assertEquals(AjaxAuthenticationValidatingListener::RESULT_NOT_BACKEND_REQUEST, $lnr->onKernelException($event));
    }

    public function testOnKernelExceptionWithInvalidException()
    {
        $event = $this->createEvent(true, '/mega-backend');

        $lnr = new AjaxAuthenticationValidatingListener('/mega-backend');
        $lnr->onKernelException($event);

        $this->assertNull($event->getResponse());
    }

    public function testOnKernelException()
    {
        $event = $this->createEvent(true, '/mega-backend', new AccessDeniedException());

        $lnr = new AjaxAuthenticationValidatingListener('/mega-backend');
        $lnr->onKernelException($event);

        /* @var JsonResponse $response */
        $response = $event->getResponse();

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $response);
        $content = json_decode($response->getContent(), true);
        $this->assertTrue(is_array($content));
        $this->assertArrayHasKey('success', $content);
        $this->assertFalse($content['success']);
        $this->assertArrayHasKey('message', $content);
        $this->assertTrue('' != $content['message']);
    }
}

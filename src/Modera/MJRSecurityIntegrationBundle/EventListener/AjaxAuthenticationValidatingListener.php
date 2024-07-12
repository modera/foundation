<?php

namespace Modera\MJRSecurityIntegrationBundle\EventListener;

use Modera\FoundationBundle\Translation\T;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * MPFE-817.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2015 Modera Foundation
 */
class AjaxAuthenticationValidatingListener
{
    // these constants are used only to simplify writing unit test
    public const RESULT_NOT_AJAX = 'not_ajax';
    public const RESULT_NOT_BACKEND_REQUEST = 'not_backend_request';

    private string $backendRoutesPrefix;

    public function __construct(string $backendRoutesPrefix)
    {
        $this->backendRoutesPrefix = $backendRoutesPrefix;
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $this->kernelExceptionHandler($event);
    }

    /**
     * @internal
     */
    public function kernelExceptionHandler(ExceptionEvent $event): ?string
    {
        if (!$event->getRequest()->isXmlHttpRequest()) {
            return self::RESULT_NOT_AJAX;
        }
        if (\substr($event->getRequest()->getPathInfo(), 0, \strlen($this->backendRoutesPrefix)) !== $this->backendRoutesPrefix) {
            return self::RESULT_NOT_BACKEND_REQUEST;
        }

        $e = $event->getThrowable();

        if ($e instanceof AccessDeniedException) {
            $msg = "Your session has expired and you need to re-login or you don't have privileges to perform given action.";

            $response = new JsonResponse(
                [
                    'success' => false,
                    'message' => T::trans($msg),
                ],
                403
            );

            $event->setResponse($response);
        }

        return null;
    }
}

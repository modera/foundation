<?php

namespace Modera\DirectBundle\Router;

use Modera\DirectBundle\Api\ControllerApi;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @copyright 2015 Modera Foundation
 */
class Router
{
    protected Request $request;

    protected Response $response;

    protected string $defaultAccess;

    protected mixed $session;

    public function __construct(
        protected readonly ContainerInterface $container,
    ) {
        /** @var RequestStack $requestStack */
        $requestStack = $container->get('request_stack');

        /** @var SymfonyRequest $request */
        $request = $requestStack->getCurrentRequest();

        /** @var string $defaultAccess */
        $defaultAccess = $container->getParameter('direct.api.default_access');

        /** @var string $sessionAttribute */
        $sessionAttribute = $container->getParameter('direct.api.session_attribute');

        /** @var SessionInterface $session */
        $session = $request->getSession();

        $this->defaultAccess = $defaultAccess;
        $this->session = $session->get($sessionAttribute);

        $this->request = $this->createRequest($request);
        $this->response = $this->createResponse();
    }

    protected function createRequest(SymfonyRequest $request): Request
    {
        return new Request($request);
    }

    protected function createResponse(): Response
    {
        return new Response($this->request->getCallType(), $this->request->isUpload());
    }

    /**
     * Do the ExtDirect routing processing.
     */
    public function route(): string
    {
        $batch = [];

        foreach ($this->request->getCalls() as $call) {
            $batch[] = $this->dispatch($call);
        }

        return $this->response->encode($batch);
    }

    /**
     * Dispatch a remote method call.
     *
     * @return ?array<mixed>
     */
    protected function dispatch(Call $call): ?array
    {
        $api = new ControllerApi($this->container, $this->getControllerClass($call->getAction()));

        $controller = $this->resolveController($call->getAction());
        $method = $call->getMethod().'Action';
        $accessType = $api->getMethodAccess($method);

        /** @var string $environment */
        $environment = $this->container->getParameter('kernel.environment');

        if (!\is_callable([$controller, $method])) {
            // TODO: throw an exception method not callable
            return null;
        } elseif ('secure' === $this->defaultAccess && 'anonymous' !== $accessType) {
            if (!$this->session) {
                $result = $call->getException(new \Exception('Access denied!'), $environment);
            }
        } elseif ('secure' === $accessType) {
            if (!$this->session) {
                $result = $call->getException(new \Exception('Access denied!'), $environment);
            }
        } elseif ('form' === $this->request->getCallType()) {
            $result = $call->getResponse($controller->$method($call->getData(), $this->request->getFiles()));
        }

        if (!isset($result)) {
            try {
                $result = $controller->$method(...$call->getData());
                $result = $call->getResponse($result);
            } catch (\Exception $e) {
                $result = $call->getException($e, $environment);
            }
        }

        return $result;
    }

    /**
     * Resolve the called controller from action.
     */
    private function resolveController(string $action): mixed
    {
        return $this->container->get($this->getControllerClass($action));
    }

    /**
     * Return the controller class name.
     */
    private function getControllerClass(string $action): string
    {
        list($bundleName, $controllerName) = \explode('_', $action);
        $bundleName .= 'Bundle';

        /** @var KernelInterface $kernel */
        $kernel = $this->container->get('kernel');

        $bundle = $kernel->getBundle($bundleName);

        $namespace = $bundle->getNamespace().'\\Controller';

        return $namespace.'\\'.$controllerName.'Controller';
    }
}

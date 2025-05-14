<?php

namespace Modera\DirectBundle\Controller;

use Modera\DirectBundle\Api\ApiFactory;
use Modera\DirectBundle\Router\RouterFactoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

/**
 * @copyright 2015 Modera Foundation
 */
#[AsController]
class DirectController extends AbstractController
{
    public function __construct(
        private readonly ApiFactory $apiFactory,
        private readonly RouterFactoryInterface $routerFactory,
    ) {
    }

    protected function isDebug(): bool
    {
        /** @var bool $isDebug */
        $isDebug = $this->getParameter('kernel.debug');

        return $isDebug;
    }

    /**
     * Generate the ExtDirect API.
     */
    public function getApiAction(): Response
    {
        // instantiate the api object
        $api = $this->apiFactory->create();

        if ($this->isDebug()) {
            $exceptionLogStr = 'console.error("Remote Call:", event);';
        } else {
            /** @var string $exceptionMessage */
            $exceptionMessage = $this->getParameter('direct.exception.message');
            $exceptionLogStr = \sprintf('console.error(%s);', \json_encode($exceptionMessage));
        }
        // create the response
        $response = new Response(\sprintf(\implode(\PHP_EOL, [
            'Ext.Direct.addProvider(%s);',
            'Ext.direct.Manager.on("exception", function(event) {',
            '    %s',
            '});',
        ]), $api, $exceptionLogStr));
        $response->headers->set('Content-Type', 'text/javascript');

        return $response;
    }

    /**
     * Generate the Remoting ExtDirect API.
     */
    public function getRemotingAction(): Response
    {
        // instantiate the api object
        $api = $this->apiFactory->create();

        // create the response
        $response = new Response(\sprintf('Ext.app.REMOTING_API = %s;', $api));
        $response->headers->set('Content-Type', 'text/javascript');

        return $response;
    }

    /**
     * Route the ExtDirect calls.
     */
    public function routeAction(Request $request): Response
    {
        // instantiate the router object
        $router = $this->routerFactory->create($request);

        // create response
        $response = new Response($router->route());
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}

<?php

namespace Modera\ModuleBundle\Listener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Modera\ModuleBundle\DependencyInjection\ModeraModuleExtension;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class MaintenanceListener
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param RequestEvent $event
     */
    public function onKernelRequest(RequestEvent $event)
    {
        if ($this->isMaintenanceMode()) {
            $request = $event->getRequest();
            if ($request->isXmlHttpRequest()) {
                $response = new JsonResponse(array(
                    'success' => false,
                    'message' => 'The server is temporarily down for maintenance.',
                ), JsonResponse::HTTP_SERVICE_UNAVAILABLE);
            } else {
                $engine = $this->container->get('twig');
                $content = $engine->render('@ModeraModule/maintenance.html.twig');
                $response = new Response($content, JsonResponse::HTTP_SERVICE_UNAVAILABLE);
            }

            $event->setResponse($response);
            $event->stopPropagation();
        }
    }

    /**
     * @return bool
     */
    protected function isMaintenanceMode()
    {
        $debug = in_array($this->container->get('kernel')->getEnvironment(), array('test', 'dev'));
        $name = ModeraModuleExtension::CONFIG_KEY . '.maintenance_mode';
        return $this->container->getParameter($name) === true && !$debug;
    }
}

<?php

namespace Modera\ModuleBundle\Listener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
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
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if ($this->isMaintenanceMode()) {
            $request = $event->getRequest();
            if ($request->isXmlHttpRequest()) {
                $result = array(
                    'success' => false,
                    'message' => 'The server is temporarily down for maintenance.',
                );
                $response = new JsonResponse($result);
            } else {
                $engine = $this->container->get('templating');
                $content = $engine->render('ModeraModuleBundle::maintenance.html.twig');
                $response = new Response($content, 503);
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

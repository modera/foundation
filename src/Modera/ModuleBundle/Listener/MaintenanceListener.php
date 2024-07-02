<?php

namespace Modera\ModuleBundle\Listener;

use Modera\ModuleBundle\DependencyInjection\ModeraModuleExtension;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class MaintenanceListener
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if ($this->isMaintenanceMode()) {
            $request = $event->getRequest();
            if ($request->isXmlHttpRequest()) {
                $response = new JsonResponse([
                    'success' => false,
                    'message' => 'The server is temporarily down for maintenance.',
                ], JsonResponse::HTTP_SERVICE_UNAVAILABLE);
            } else {
                /** @var \Twig\Environment $engine */
                $engine = $this->container->get('twig');
                $content = $engine->render('@ModeraModule/maintenance.html.twig');
                $response = new Response($content, JsonResponse::HTTP_SERVICE_UNAVAILABLE);
            }

            $event->setResponse($response);
            $event->stopPropagation();
        }
    }

    protected function isMaintenanceMode(): bool
    {
        /** @var KernelInterface $kernel */
        $kernel = $this->container->get('kernel');
        $debug = \in_array($kernel->getEnvironment(), ['test', 'dev']);
        $name = ModeraModuleExtension::CONFIG_KEY.'.maintenance_mode';

        return true === $this->container->getParameter($name) && !$debug;
    }
}

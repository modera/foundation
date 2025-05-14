<?php

namespace Modera\MjrIntegrationBundle\Controller;

use Modera\MjrIntegrationBundle\Config\ConfigManager;
use Modera\MjrIntegrationBundle\Model\FontAwesome;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Exposes actions which can be used by client-side runtime to configure/manage its state.
 *
 * @copyright 2013 Modera Foundation
 */
#[AsController]
class IndexController extends AbstractController
{
    public function __construct(
        private readonly ConfigManager $configManager,
    ) {
    }

    #[Route(path: '/get-config', name: 'mf_get_config')]
    public function getConfigAction(): JsonResponse
    {
        return new JsonResponse(\json_encode($this->configManager->getConfig(), \JSON_PRETTY_PRINT), Response::HTTP_OK, [], true);
    }

    public function fontAwesomeJsAction(): Response
    {
        $response = new Response(FontAwesome::jsCode());
        $response->headers->set('Content-Type', 'text/javascript');

        return $response;
    }

    public function fontAwesomeCssAction(): Response
    {
        $response = new Response(FontAwesome::cssCode());
        $response->headers->set('Content-Type', 'text/css');

        return $response;
    }
}

<?php

namespace Modera\MjrIntegrationBundle\Controller;

use Modera\MjrIntegrationBundle\Config\ConfigManager;
use Modera\MjrIntegrationBundle\Model\FontAwesome;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Exposes actions which can be used by client-side runtime to configure/manage its state.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2013 Modera Foundation
 */
class IndexController extends Controller
{
    /**
     * @Route("/get-config", name="mf_get_config")
     */
    public function getConfigAction(): JsonResponse
    {
        /** @var ConfigManager $configManager */
        $configManager = $this->container->get('modera_mjr_integration.config.config_manager');

        return new JsonResponse(\json_encode($configManager->getConfig(), \JSON_PRETTY_PRINT), Response::HTTP_OK, [], true);
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

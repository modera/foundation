<?php

namespace Modera\DynamicallyConfigurableMJRBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Modera\ConfigBundle\Manager\ConfigurationEntriesManagerInterface;
use Modera\DynamicallyConfigurableMJRBundle\ModeraDynamicallyConfigurableMJRBundle as Bundle;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2020 Modera Foundation
 */
class IndexController extends Controller
{
    /**
     * @return Response
     */
    public function logoCssAction()
    {
        /* @var ConfigurationEntriesManagerInterface $mgr */
        $mgr = $this->get('modera_config.configuration_entries_manager');

        $logoUrl = $mgr->findOneByNameOrDie(Bundle::CONFIG_LOGO_URL)->getValue();

        $content = $this->renderView('ModeraDynamicallyConfigurableMJRBundle::logo.css.twig', array(
            'logo_url' => $logoUrl,
        ));

        $response = new Response($content);
        $response->headers->set('Content-Type', 'text/css');

        return $response;
    }
}

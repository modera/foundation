<?php

namespace Modera\DynamicallyConfigurableMJRBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as Controller;
use Modera\ConfigBundle\Manager\ConfigurationEntriesManagerInterface;
use Modera\DynamicallyConfigurableMJRBundle\Resolver\ValueResolverInterface;
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

        /* @var ValueResolverInterface $resolver */
        $resolver = $this->get('modera_dynamically_configurable_mjr.resolver.value_resolver');

        $logoUrl = $mgr->findOneByNameOrDie(Bundle::CONFIG_LOGO_URL)->getValue();

        $content = $this->renderView('ModeraDynamicallyConfigurableMJRBundle::logo.css.twig', array(
            'logo_url' => $resolver->resolve(Bundle::CONFIG_LOGO_URL, $logoUrl),
        ));

        $response = new Response($content);
        $response->headers->set('Content-Type', 'text/css');

        return $response;
    }
}

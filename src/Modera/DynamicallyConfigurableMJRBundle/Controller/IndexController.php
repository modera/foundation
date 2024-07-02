<?php

namespace Modera\DynamicallyConfigurableMJRBundle\Controller;

use Modera\ConfigBundle\Manager\ConfigurationEntriesManagerInterface;
use Modera\DynamicallyConfigurableMJRBundle\ModeraDynamicallyConfigurableMJRBundle as Bundle;
use Modera\DynamicallyConfigurableMJRBundle\Resolver\ValueResolverInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2020 Modera Foundation
 */
class IndexController extends Controller
{
    public function logoCssAction(): Response
    {
        /** @var ConfigurationEntriesManagerInterface $mgr */
        $mgr = $this->container->get('modera_config.configuration_entries_manager');

        /** @var ValueResolverInterface $resolver */
        $resolver = $this->container->get('modera_dynamically_configurable_mjr.resolver.value_resolver');

        $logoUrl = $mgr->findOneByNameOrDie(Bundle::CONFIG_LOGO_URL)->getValue();

        $content = $this->renderView('@ModeraDynamicallyConfigurableMJR/logo.css.twig', [
            'logo_url' => $resolver->resolve(Bundle::CONFIG_LOGO_URL, $logoUrl),
        ]);

        $response = new Response($content);
        $response->headers->set('Content-Type', 'text/css');

        return $response;
    }
}

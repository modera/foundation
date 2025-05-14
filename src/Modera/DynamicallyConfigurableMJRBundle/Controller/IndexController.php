<?php

namespace Modera\DynamicallyConfigurableMJRBundle\Controller;

use Modera\ConfigBundle\Manager\ConfigurationEntriesManagerInterface;
use Modera\DynamicallyConfigurableMJRBundle\ModeraDynamicallyConfigurableMJRBundle as Bundle;
use Modera\DynamicallyConfigurableMJRBundle\Resolver\ValueResolverInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

/**
 * @copyright 2020 Modera Foundation
 */
#[AsController]
class IndexController extends AbstractController
{
    public function __construct(
        private readonly ConfigurationEntriesManagerInterface $configurationEntriesManager,
        private readonly ValueResolverInterface $valueResolver,
    ) {
    }

    public function logoCssAction(): Response
    {
        $logoUrl = $this->configurationEntriesManager->findOneByNameOrDie(Bundle::CONFIG_LOGO_URL)->getValue();

        $content = $this->renderView('@ModeraDynamicallyConfigurableMJR/logo.css.twig', [
            'logo_url' => $this->valueResolver->resolve(Bundle::CONFIG_LOGO_URL, $logoUrl),
        ]);

        $response = new Response($content);
        $response->headers->set('Content-Type', 'text/css');

        return $response;
    }
}
